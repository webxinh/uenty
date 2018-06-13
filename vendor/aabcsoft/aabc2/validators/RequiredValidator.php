<?php


namespace aabc\validators;

use Aabc;


class RequiredValidator extends Validator
{
    
    public $skipOnEmpty = false;
    
    public $requiredValue;
    
    public $strict = false;
    
    public $message;


    
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = $this->requiredValue === null ? Aabc::t('aabc', 'Thiếu')
                : Aabc::t('aabc', '{attribute} must be "{requiredValue}".');

            // $this->message = $this->requiredValue === null ? Aabc::t('aabc', 'Vui lòng nhập {attribute}.')
            //     : Aabc::t('aabc', '{attribute} must be "{requiredValue}".');
        }
    }

    
    protected function validateValue($value)
    {
        if ($this->requiredValue === null) {
            if ($this->strict && $value !== null || !$this->strict && !$this->isEmpty(is_string($value) ? trim($value) : $value)) {
                return null;
            }
        } elseif (!$this->strict && $value == $this->requiredValue || $this->strict && $value === $this->requiredValue) {
            return null;
        }
        if ($this->requiredValue === null) {
            return [$this->message, []];
        } else {
            return [$this->message, [
                'requiredValue' => $this->requiredValue,
            ]];
        }
    }

    
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'aabc.validation.required(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

    
    public function getClientOptions($model, $attribute)
    {
        $options = [];
        if ($this->requiredValue !== null) {
            $options['message'] = Aabc::$app->getI18n()->format($this->message, [
                'requiredValue' => $this->requiredValue,
            ], Aabc::$app->language);
            $options['requiredValue'] = $this->requiredValue;
        } else {
            $options['message'] = $this->message;
        }
        if ($this->strict) {
            $options['strict'] = 1;
        }

        $options['message'] = Aabc::$app->getI18n()->format($options['message'], [
            'attribute' => $model->getAttributeLabel($attribute),
        ], Aabc::$app->language);

        return $options;
    }
}
