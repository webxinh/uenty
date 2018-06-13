<?php


namespace aabc\behaviors;

use aabc\base\InvalidCallException;
use aabc\db\BaseActiveRecord;


class TimestampBehavior extends AttributeBehavior
{
    
    public $createdAtAttribute = 'created_at';
    
    public $updatedAtAttribute = 'updated_at';
    
    public $value;


    
    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [
                BaseActiveRecord::EVENT_BEFORE_INSERT => [$this->createdAtAttribute, $this->updatedAtAttribute],
                BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->updatedAtAttribute,
            ];
        }
    }

    
    protected function getValue($event)
    {
        if ($this->value === null) {
            return time();
        }
        return parent::getValue($event);
    }

    
    public function touch($attribute)
    {
        /* @var $owner BaseActiveRecord */
        $owner = $this->owner;
        if ($owner->getIsNewRecord()) {
            throw new InvalidCallException('Updating the timestamp is not possible on a new record.');
        }
        $owner->updateAttributes(array_fill_keys((array) $attribute, $this->getValue(null)));
    }
}
