<?php


namespace aabc\validators;

use Aabc;
use aabc\base\Model;
use aabc\db\ActiveQuery;
use aabc\db\ActiveQueryInterface;
use aabc\db\ActiveRecordInterface;
use aabc\db\Query;
use aabc\helpers\Inflector;


class UniqueValidator extends Validator
{
    
    public $targetClass;
    
    public $targetAttribute;
    
    public $filter;
    
    public $message;
    
    public $comboNotUnique;
    
    public $targetAttributeJunction = 'and';


    
    public function init()
    {
        parent::init();
        if ($this->message !== null) {
            return;
        }
        if (is_array($this->targetAttribute) && count($this->targetAttribute) > 1) {
            // fallback for deprecated `comboNotUnique` property - use it as message if is set
            if ($this->comboNotUnique === null) {
                $this->message = Aabc::t('aabc', 'The combination {values} of {attributes} has already been taken.');
            } else {
                $this->message = $this->comboNotUnique;
            }
        } else {
            $this->message = Aabc::t('aabc', '{attribute} "{value}" has already been taken.');
        }
    }

    
    public function validateAttribute($model, $attribute)
    {
        /* @var $targetClass ActiveRecordInterface */
        $targetClass = $this->targetClass === null ? get_class($model) : $this->targetClass;
        $targetAttribute = $this->targetAttribute === null ? $attribute : $this->targetAttribute;
        $rawConditions = $this->prepareConditions($targetAttribute, $model, $attribute);
        $conditions[] = $this->targetAttributeJunction === 'or' ? 'or' : 'and';

        foreach ($rawConditions as $key => $value) {
            if (is_array($value)) {
                $this->addError($model, $attribute, Aabc::t('aabc', '{attribute} is invalid.'));
                return;
            }
            $conditions[] = [$key => $value];
        }

        if ($this->modelExists($targetClass, $conditions, $model)) {
            if (count($targetAttribute) > 1) {
                $this->addComboNotUniqueError($model, $attribute);
            } else {
                $this->addError($model, $attribute, $this->message);
            }
        }
    }

    
    private function modelExists($targetClass, $conditions, $model)
    {
        
        $query = $this->prepareQuery($targetClass, $conditions);

        if (!$model instanceof ActiveRecordInterface || $model->getIsNewRecord() || $model->className() !== $targetClass::className()) {
            // if current $model isn't in the database yet then it's OK just to call exists()
            // also there's no need to run check based on primary keys, when $targetClass is not the same as $model's class
            $exists = $query->exists();
        } else {
            // if current $model is in the database already we can't use exists()
            if ($query instanceof \aabc\db\ActiveQuery) {
                // only select primary key to optimize query
                $query->select($targetClass::primaryKey());
            }
            $models = $query->limit(2)->asArray()->all();
            $n = count($models);
            if ($n === 1) {
                // if there is one record, check if it is the currently validated model
                $dbModel = reset($models);
                $pks = $targetClass::primaryKey();
                $pk = [];
                foreach($pks as $pkAttribute) {
                    $pk[$pkAttribute] = $dbModel[$pkAttribute];
                }
                $exists = ($pk != $model->getOldPrimaryKey(true));
            } else {
                // if there is more than one record, the value is not unique
                $exists = $n > 1;
            }
        }

        return $exists;
    }

    
    private function prepareQuery($targetClass, $conditions)
    {
        $query = $targetClass::find();
        $query->andWhere($conditions);
        if ($this->filter instanceof \Closure) {
            call_user_func($this->filter, $query);
        } elseif ($this->filter !== null) {
            $query->andWhere($this->filter);
        }

        return $query;
    }

    
    private function prepareConditions($targetAttribute, $model, $attribute)
    {
        if (is_array($targetAttribute)) {
            $conditions = [];
            foreach ($targetAttribute as $k => $v) {
                $conditions[$v] = is_int($k) ? $model->$v : $model->$k;
            }
        } else {
            $conditions = [$targetAttribute => $model->$attribute];
        }

        return $conditions;
    }

    
    private function addComboNotUniqueError($model, $attribute)
    {
        $attributeCombo = [];
        $valueCombo = [];
        foreach ($this->targetAttribute as $key => $value) {
            if(is_int($key)) {
                $attributeCombo[] = $model->getAttributeLabel($value);
                $valueCombo[] = '"' . $model->$value . '"';
            } else {
                $attributeCombo[] = $model->getAttributeLabel($key);
                $valueCombo[] = '"' . $model->$key . '"';
            }
        }
        $this->addError($model, $attribute, $this->message, [
            'attributes' => Inflector::sentence($attributeCombo),
            'values' => implode('-', $valueCombo)
        ]);
    }
}
