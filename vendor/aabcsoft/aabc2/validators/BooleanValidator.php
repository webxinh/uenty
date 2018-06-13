<?php


namespace aabc\validators;

use Aabc;


class BooleanValidator extends Validator
{
    
    public $trueValue = '1';
    
    public $falseValue = '0';
    
    public $strict = false;


    
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Aabc::t('aabc', '{attribute} must be either "{true}" or "{false}".');
        }
    }

    
    protected function validateValue($value)
    {
        $valid = !$this->strict && ($value == $this->trueValue || $value == $this->falseValue)
                 || $this->strict && ($value === $this->trueValue || $value === $this->falseValue);

        if (!$valid) {
            return [$this->message, [
                'true' => $this->trueValue === true ? 'true' : $this->trueValue,
                'false' => $this->falseValue === false ? 'false' : $this->falseValue,
            ]];
        }

        return null;
    }

    
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'aabc.validation.boolean(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

    
    public function getClientOptions($model, $attribute)
    {
        $options = [
            'trueValue' => $this->trueValue,
            'falseValue' => $this->falseValue,
            'message' => Aabc::$app->getI18n()->format($this->message, [
                'attribute' => $model->getAttributeLabel($attribute),
                'true' => $this->trueValue === true ? 'true' : $this->trueValue,
                'false' => $this->falseValue === false ? 'false' : $this->falseValue,
            ], Aabc::$app->language),
        ];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }
        if ($this->strict) {
            $options['strict'] = 1;
        }

        return $options;
    }
}
