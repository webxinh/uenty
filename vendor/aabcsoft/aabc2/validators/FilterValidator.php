<?php


namespace aabc\validators;

use aabc\base\InvalidConfigException;


class FilterValidator extends Validator
{
    
    public $filter;
    
    public $skipOnArray = false;
    
    public $skipOnEmpty = false;


    
    public function init()
    {
        parent::init();
        if ($this->filter === null) {
            throw new InvalidConfigException('The "filter" property must be set.');
        }
    }

    
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (!$this->skipOnArray || !is_array($value)) {
            $model->$attribute = call_user_func($this->filter, $value);
        }
    }

    
    public function clientValidateAttribute($model, $attribute, $view)
    {
        if ($this->filter !== 'trim') {
            return null;
        }

        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'value = aabc.validation.trim($form, attribute, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

    
    public function getClientOptions($model, $attribute)
    {
        $options = [];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}
