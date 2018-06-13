<?php


namespace aabc\validators;

use Aabc;
use aabc\base\InvalidConfigException;


class ExistValidator extends Validator
{
    
    public $targetClass;
    
    public $targetAttribute;
    
    public $filter;
    
    public $allowArray = false;
    
    public $targetAttributeJunction = 'and';


    
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Aabc::t('aabc', '{attribute} is invalid.');
        }
    }

    
    public function validateAttribute($model, $attribute)
    {
        $targetAttribute = $this->targetAttribute === null ? $attribute : $this->targetAttribute;
        $params = $this->prepareConditions($targetAttribute, $model, $attribute);
        $conditions[] = $this->targetAttributeJunction == 'or' ? 'or' : 'and';

        if (!$this->allowArray) {
            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    $this->addError($model, $attribute, Aabc::t('aabc', '{attribute} is invalid.'));

                    return;
                }
                $conditions[] = [$key => $value];
            }
        } else {
            $conditions[] = $params;
        }

        $targetClass = $this->targetClass === null ? get_class($model) : $this->targetClass;
        $query = $this->createQuery($targetClass, $conditions);

        if (is_array($model->$attribute)) {
            if ($query->count("DISTINCT [[$targetAttribute]]") != count($model->$attribute)) {
                $this->addError($model, $attribute, $this->message);
            }
        } elseif (!$query->exists()) {
            $this->addError($model, $attribute, $this->message);
        }
    }

    
    private function prepareConditions($targetAttribute, $model, $attribute)
    {
        if (is_array($targetAttribute)) {
            if ($this->allowArray) {
                throw new InvalidConfigException('The "targetAttribute" property must be configured as a string.');
            }
            $params = [];
            foreach ($targetAttribute as $k => $v) {
                $params[$v] = is_int($k) ? $model->$attribute : $model->$k;
            }
        } else {
            $params = [$targetAttribute => $model->$attribute];
        }
        return $params;
    }

    
    protected function validateValue($value)
    {
        if ($this->targetClass === null) {
            throw new InvalidConfigException('The "targetClass" property must be set.');
        }
        if (!is_string($this->targetAttribute)) {
            throw new InvalidConfigException('The "targetAttribute" property must be configured as a string.');
        }

        $query = $this->createQuery($this->targetClass, [$this->targetAttribute => $value]);

        if (is_array($value)) {
            if (!$this->allowArray) {
                return [$this->message, []];
            }
            return $query->count("DISTINCT [[$this->targetAttribute]]") == count($value) ? null : [$this->message, []];
        } else {
            return $query->exists() ? null : [$this->message, []];
        }
    }

    
    protected function createQuery($targetClass, $condition)
    {
        /* @var $targetClass \aabc\db\ActiveRecordInterface */
        $query = $targetClass::find()->andWhere($condition);
        if ($this->filter instanceof \Closure) {
            call_user_func($this->filter, $query);
        } elseif ($this->filter !== null) {
            $query->andWhere($this->filter);
        }

        return $query;
    }
}
