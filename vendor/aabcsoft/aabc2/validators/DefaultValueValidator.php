<?php


namespace aabc\validators;


class DefaultValueValidator extends Validator
{
    
    public $value;
    
    public $skipOnEmpty = false;


    
    public function validateAttribute($model, $attribute)
    {
        if ($this->isEmpty($model->$attribute)) {
            if ($this->value instanceof \Closure) {
                $model->$attribute = call_user_func($this->value, $model, $attribute);
            } else {
                $model->$attribute = $this->value;
            }
        }
    }
}
