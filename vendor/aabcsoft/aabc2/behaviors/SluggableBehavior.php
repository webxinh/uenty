<?php


namespace aabc\behaviors;

use aabc\base\InvalidConfigException;
use aabc\db\BaseActiveRecord;
use aabc\helpers\ArrayHelper;
use aabc\helpers\Inflector;
use aabc\validators\UniqueValidator;
use Aabc;


class SluggableBehavior extends AttributeBehavior
{
    
    public $slugAttribute = 'slug';
    
    public $attribute;
    
    public $value;
    
    public $immutable = false;
    
    public $ensureUnique = false;
    
    public $uniqueValidator = [];
    
    public $uniqueSlugGenerator;


    
    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [BaseActiveRecord::EVENT_BEFORE_VALIDATE => $this->slugAttribute];
        }

        if ($this->attribute === null && $this->value === null) {
            throw new InvalidConfigException('Either "attribute" or "value" property must be specified.');
        }
    }

    
    protected function getValue($event)
    {
        if ($this->attribute !== null) {
            if ($this->isNewSlugNeeded()) {
                $slugParts = [];
                foreach ((array) $this->attribute as $attribute) {
                    $slugParts[] = ArrayHelper::getValue($this->owner, $attribute);
                }

                $slug = $this->generateSlug($slugParts);
            } else {
                return $this->owner->{$this->slugAttribute};
            }
        } else {
            $slug = parent::getValue($event);
        }

        return $this->ensureUnique ? $this->makeUnique($slug) : $slug;
    }

    
    protected function isNewSlugNeeded()
    {
        if (empty($this->owner->{$this->slugAttribute})) {
            return true;
        }

        if ($this->immutable) {
            return false;
        }

        foreach ((array)$this->attribute as $attribute) {
            if ($this->owner->isAttributeChanged($attribute)) {
                return true;
            }
        }

        return false;
    }

    
    protected function generateSlug($slugParts)
    {
        return Inflector::slug(implode('-', $slugParts));
    }

    
    protected function makeUnique($slug)
    {
        $uniqueSlug = $slug;
        $iteration = 0;
        while (!$this->validateSlug($uniqueSlug)) {
            $iteration++;
            $uniqueSlug = $this->generateUniqueSlug($slug, $iteration);
        }
        return $uniqueSlug;
    }

    
    protected function validateSlug($slug)
    {
        /* @var $validator UniqueValidator */
        /* @var $model BaseActiveRecord */
        $validator = Aabc::createObject(array_merge(
            [
                'class' => UniqueValidator::className(),
            ],
            $this->uniqueValidator
        ));

        $model = clone $this->owner;
        $model->clearErrors();
        $model->{$this->slugAttribute} = $slug;

        $validator->validateAttribute($model, $this->slugAttribute);
        return !$model->hasErrors();
    }

    
    protected function generateUniqueSlug($baseSlug, $iteration)
    {
        if (is_callable($this->uniqueSlugGenerator)) {
            return call_user_func($this->uniqueSlugGenerator, $baseSlug, $iteration, $this->owner);
        }
        return $baseSlug . '-' . ($iteration + 1);
    }
}
