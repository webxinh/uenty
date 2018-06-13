<?php


namespace aabc\validators;

use Aabc;
use aabc\helpers\StringHelper;
use aabc\web\JsExpression;
use aabc\helpers\Json;


class NumberValidator extends Validator
{
    
    public $integerOnly = false;
    
    public $max;
    
    public $min;
    
    public $tooBig;
    
    public $tooSmall;
    
    public $integerPattern = '/^\s*[+-]?\d+\s*$/';
    
    public $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';


    
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = $this->integerOnly ? Aabc::t('aabc', 'Phải là số')
                : Aabc::t('aabc', '{attribute} phải là số.');
        }
        if ($this->min !== null && $this->tooSmall === null) {
            $this->tooSmall = Aabc::t('aabc', 'Phải lớn hơn {min}');
        }
        if ($this->max !== null && $this->tooBig === null) {
            $this->tooBig = Aabc::t('aabc', 'Phải nhỏ hơn {max}');
        }
    }

    
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (is_array($value) || (is_object($value) && !method_exists($value, '__toString'))) {
            $this->addError($model, $attribute, $this->message);
            return;
        }
        $pattern = $this->integerOnly ? $this->integerPattern : $this->numberPattern;

        if (!preg_match($pattern, StringHelper::normalizeNumber($value))) {
            $this->addError($model, $attribute, $this->message);
        }
        if ($this->min !== null && $value < $this->min) {
            $this->addError($model, $attribute, $this->tooSmall, ['min' => $this->min]);
        }
        if ($this->max !== null && $value > $this->max) {
            $this->addError($model, $attribute, $this->tooBig, ['max' => $this->max]);
        }
    }

    
    protected function validateValue($value)
    {
        if (is_array($value) || is_object($value)) {
            return [Aabc::t('aabc', '{attribute} is invalid.'), []];
        }
        $pattern = $this->integerOnly ? $this->integerPattern : $this->numberPattern;
        if (!preg_match($pattern, StringHelper::normalizeNumber($value))) {
            return [$this->message, []];
        } elseif ($this->min !== null && $value < $this->min) {
            return [$this->tooSmall, ['min' => $this->min]];
        } elseif ($this->max !== null && $value > $this->max) {
            return [$this->tooBig, ['max' => $this->max]];
        } else {
            return null;
        }
    }

    
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'aabc.validation.number(value, messages, ' . Json::htmlEncode($options) . ');';
    }

    
    public function getClientOptions($model, $attribute)
    {
        $label = $model->getAttributeLabel($attribute);

        $options = [
            'pattern' => new JsExpression($this->integerOnly ? $this->integerPattern : $this->numberPattern),
            'message' => Aabc::$app->getI18n()->format($this->message, [
                'attribute' => $label,
            ], Aabc::$app->language),
        ];

        if ($this->min !== null) {
            // ensure numeric value to make javascript comparison equal to PHP comparison
            // https://github.com/aabcsoft/aabc2/issues/3118
            $options['min'] = is_string($this->min) ? (float) $this->min : $this->min;
            $options['tooSmall'] = Aabc::$app->getI18n()->format($this->tooSmall, [
                'attribute' => $label,
                'min' => $this->min,
            ], Aabc::$app->language);
        }
        if ($this->max !== null) {
            // ensure numeric value to make javascript comparison equal to PHP comparison
            // https://github.com/aabcsoft/aabc2/issues/3118
            $options['max'] = is_string($this->max) ? (float) $this->max : $this->max;
            $options['tooBig'] = Aabc::$app->getI18n()->format($this->tooBig, [
                'attribute' => $label,
                'max' => $this->max,
            ], Aabc::$app->language);
        }
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}
