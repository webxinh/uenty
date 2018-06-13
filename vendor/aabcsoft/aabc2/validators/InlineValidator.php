<?php


namespace aabc\validators;


class InlineValidator extends Validator
{
    
    public $method;
    
    public $params;
    
    public $clientValidate;


    
    public function validateAttribute($model, $attribute)
    {
        $method = $this->method;
        if (is_string($method)) {
            $method = [$model, $method];
        }
        call_user_func($method, $attribute, $this->params, $this);
    }

    
    public function clientValidateAttribute($model, $attribute, $view)
    {
        if ($this->clientValidate !== null) {
            $method = $this->clientValidate;
            if (is_string($method)) {
                $method = [$model, $method];
            }

            return call_user_func($method, $attribute, $this->params, $this);
        } else {
            return null;
        }
    }
}
