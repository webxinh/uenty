<?php


namespace aabc\validators;

use aabc\base\InvalidConfigException;
use Aabc;
use aabc\base\Model;


class EachValidator extends Validator
{
    
    public $rule;
    
    public $allowMessageFromRule = true;
    
    public $stopOnFirstError = true;

    
    private $_validator;


    
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Aabc::t('aabc', '{attribute} is invalid.');
        }
    }

    
    private function getValidator($model = null)
    {
        if ($this->_validator === null) {
            $this->_validator = $this->createEmbeddedValidator($model);
        }
        return $this->_validator;
    }

    
    private function createEmbeddedValidator($model)
    {
        $rule = $this->rule;
        if ($rule instanceof Validator) {
            return $rule;
        } elseif (is_array($rule) && isset($rule[0])) { // validator type
            if (!is_object($model)) {
                $model = new Model(); // mock up context model
            }
            return Validator::createValidator($rule[0], $model, $this->attributes, array_slice($rule, 1));
        } else {
            throw new InvalidConfigException('Invalid validation rule: a rule must be an array specifying validator type.');
        }
    }

    
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (!is_array($value)) {
            $this->addError($model, $attribute, $this->message, []);
            return;
        }

        $validator = $this->getValidator($model); // ensure model context while validator creation

        $detectedErrors = $model->getErrors($attribute);
        $filteredValue = $model->$attribute;
        foreach ($value as $k => $v) {
            $model->clearErrors($attribute);
            $model->$attribute = $v;
            if (!$validator->skipOnEmpty || !$validator->isEmpty($v)) {
                $validator->validateAttribute($model, $attribute);
            }
            $filteredValue[$k] = $model->$attribute;
            if ($model->hasErrors($attribute)) {
                if ($this->allowMessageFromRule) {
                    $validationErrors = $model->getErrors($attribute);
                    $detectedErrors = array_merge($detectedErrors, $validationErrors);
                } else {
                    $model->clearErrors($attribute);
                    $this->addError($model, $attribute, $this->message, ['value' => $v]);
                    $detectedErrors[] = $model->getFirstError($attribute);
                }
                $model->$attribute = $value;

                if ($this->stopOnFirstError) {
                    break;
                }
            }
        }

        $model->$attribute = $filteredValue;
        $model->clearErrors($attribute);
        $model->addErrors([$attribute => $detectedErrors]);
    }

    
    protected function validateValue($value)
    {
        if (!is_array($value)) {
            return [$this->message, []];
        }

        $validator = $this->getValidator();
        foreach ($value as $v) {
            if ($validator->skipOnEmpty && $validator->isEmpty($v)) {
                continue;
            }
            $result = $validator->validateValue($v);
            if ($result !== null) {
                if ($this->allowMessageFromRule) {
                    $result[1]['value'] = $v;
                    return $result;
                } else {
                    return [$this->message, ['value' => $v]];
                }
            }
        }

        return null;
    }
}
