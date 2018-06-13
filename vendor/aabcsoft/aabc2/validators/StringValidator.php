<?php


namespace aabc\validators;

use Aabc;


class StringValidator extends Validator
{
    
    public $length;
    
    public $max;
    
    public $min;
    
    public $message;
    
    public $tooShort;
    
    public $tooLong;
    
    public $notEqual;
    
    public $encoding;


    
    public function init()
    {
        parent::init();
        if (is_array($this->length)) {
            if (isset($this->length[0])) {
                $this->min = $this->length[0];
            }
            if (isset($this->length[1])) {
                $this->max = $this->length[1];
            }
            $this->length = null;
        }
        if ($this->encoding === null) {
            $this->encoding = Aabc::$app->charset;
        }
        if ($this->message === null) {
            $this->message = Aabc::t('aabc', '{attribute} must be a string.');
        }
        if ($this->min !== null && $this->tooShort === null) {
            $this->tooShort = Aabc::t('aabc', '{attribute} should contain at least {min, number} {min, plural, one{character} other{characters}}.');
        }
        if ($this->max !== null && $this->tooLong === null) {
            $this->tooLong = Aabc::t('aabc', '{attribute} should contain at most {max, number} {max, plural, one{character} other{characters}}.');
        }
        if ($this->length !== null && $this->notEqual === null) {
            $this->notEqual = Aabc::t('aabc', '{attribute} should contain {length, number} {length, plural, one{character} other{characters}}.');
        }
    }

    
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        if (!is_string($value)) {
            $this->addError($model, $attribute, $this->message);

            return;
        }

        $length = mb_strlen($value, $this->encoding);

        if ($this->min !== null && $length < $this->min) {
            $this->addError($model, $attribute, $this->tooShort, ['min' => $this->min]);
        }
        if ($this->max !== null && $length > $this->max) {
            $this->addError($model, $attribute, $this->tooLong, ['max' => $this->max]);
        }
        if ($this->length !== null && $length !== $this->length) {
            $this->addError($model, $attribute, $this->notEqual, ['length' => $this->length]);
        }
    }

    
    protected function validateValue($value)
    {
        if (!is_string($value)) {
            return [$this->message, []];
        }

        $length = mb_strlen($value, $this->encoding);

        if ($this->min !== null && $length < $this->min) {
            return [$this->tooShort, ['min' => $this->min]];
        }
        if ($this->max !== null && $length > $this->max) {
            return [$this->tooLong, ['max' => $this->max]];
        }
        if ($this->length !== null && $length !== $this->length) {
            return [$this->notEqual, ['length' => $this->length]];
        }

        return null;
    }

    
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'aabc.validation.string(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

    public function getClientOptions($model, $attribute)
    {
        $label = $model->getAttributeLabel($attribute);

        $options = [
            'message' => Aabc::$app->getI18n()->format($this->message, [
                'attribute' => $label,
            ], Aabc::$app->language),
        ];

        if ($this->min !== null) {
            $options['min'] = $this->min;
            $options['tooShort'] = Aabc::$app->getI18n()->format($this->tooShort, [
                'attribute' => $label,
                'min' => $this->min,
            ], Aabc::$app->language);
        }
        if ($this->max !== null) {
            $options['max'] = $this->max;
            $options['tooLong'] = Aabc::$app->getI18n()->format($this->tooLong, [
                'attribute' => $label,
                'max' => $this->max,
            ], Aabc::$app->language);
        }
        if ($this->length !== null) {
            $options['is'] = $this->length;
            $options['notEqual'] = Aabc::$app->getI18n()->format($this->notEqual, [
                'attribute' => $label,
                'length' => $this->length,
            ], Aabc::$app->language);
        }
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}
