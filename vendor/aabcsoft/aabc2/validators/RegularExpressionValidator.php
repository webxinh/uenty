<?php


namespace aabc\validators;

use Aabc;
use aabc\base\InvalidConfigException;
use aabc\helpers\Html;
use aabc\web\JsExpression;
use aabc\helpers\Json;


class RegularExpressionValidator extends Validator
{
    
    public $pattern;
    
    public $not = false;


    
    public function init()
    {
        parent::init();
        if ($this->pattern === null) {
            throw new InvalidConfigException('The "pattern" property must be set.');
        }
        if ($this->message === null) {
            $this->message = Aabc::t('aabc', '{attribute} is invalid.');
        }
    }

    
    protected function validateValue($value)
    {
        $valid = !is_array($value) &&
            (!$this->not && preg_match($this->pattern, $value)
            || $this->not && !preg_match($this->pattern, $value));

        return $valid ? null : [$this->message, []];
    }

    
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'aabc.validation.regularExpression(value, messages, ' . Json::htmlEncode($options) . ');';
    }

    
    public function getClientOptions($model, $attribute)
    {
        $pattern = Html::escapeJsRegularExpression($this->pattern);

        $options = [
            'pattern' => new JsExpression($pattern),
            'not' => $this->not,
            'message' => Aabc::$app->getI18n()->format($this->message, [
                'attribute' => $model->getAttributeLabel($attribute),
            ], Aabc::$app->language),
        ];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}
