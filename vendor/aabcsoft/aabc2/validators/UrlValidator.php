<?php


namespace aabc\validators;

use Aabc;
use aabc\base\InvalidConfigException;
use aabc\web\JsExpression;
use aabc\helpers\Json;


class UrlValidator extends Validator
{
    // public $pattern = '/^{schemes}:\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(?::\d{1,5})?(?:$|[?\/#])/i';
    public $pattern = '/^{schemes}:\/\/(([A-Z0-9][A-Z0-9_-]*)([A-Z0-9][A-Z0-9_-]*)+)(?::\d{1,5})?(?:$|[?\/#])/i';
    
    public $validSchemes = ['http', 'https'];
    
    public $defaultScheme;
    
    public $enableIDN = false;


    
    public function init()
    {
        parent::init();
        if ($this->enableIDN && !function_exists('idn_to_ascii')) {
            throw new InvalidConfigException('In order to use IDN validation intl extension must be installed and enabled.');
        }
        if ($this->message === null) {
            $this->message = Aabc::t('aabc', '{attribute} is not a valid URL.');
        }
    }

    
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        $result = $this->validateValue($value);
        if (!empty($result)) {
            $this->addError($model, $attribute, $result[0], $result[1]);
        } elseif ($this->defaultScheme !== null && strpos($value, '://') === false) {
            $model->$attribute = $this->defaultScheme . '://' . $value;
        }
    }

    
    protected function validateValue($value)
    {
        // make sure the length is limited to avoid DOS attacks
        if (is_string($value) && strlen($value) < 2000) {
            if ($this->defaultScheme !== null && strpos($value, '://') === false) {
                $value = $this->defaultScheme . '://' . $value;
            }

            if (strpos($this->pattern, '{schemes}') !== false) {
                $pattern = str_replace('{schemes}', '(' . implode('|', $this->validSchemes) . ')', $this->pattern);
            } else {
                $pattern = $this->pattern;
            }

            if ($this->enableIDN) {
                $value = preg_replace_callback('/:\/\/([^\/]+)/', function ($matches) {
                    return '://' . idn_to_ascii($matches[1]);
                }, $value);
            }

            if (preg_match($pattern, $value)) {
                return null;
            }
        }

        return [$this->message, []];
    }

    
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        if ($this->enableIDN) {
            PunycodeAsset::register($view);
        }
        $options = $this->getClientOptions($model, $attribute);

        return 'aabc.validation.url(value, messages, ' . Json::htmlEncode($options) . ');';
    }

    
    public function getClientOptions($model, $attribute)
    {
        if (strpos($this->pattern, '{schemes}') !== false) {
            $pattern = str_replace('{schemes}', '(' . implode('|', $this->validSchemes) . ')', $this->pattern);
        } else {
            $pattern = $this->pattern;
        }

        $options = [
            'pattern' => new JsExpression($pattern),
            'message' => Aabc::$app->getI18n()->format($this->message, [
                'attribute' => $model->getAttributeLabel($attribute),
            ], Aabc::$app->language),
            'enableIDN' => (bool) $this->enableIDN,
        ];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }
        if ($this->defaultScheme !== null) {
            $options['defaultScheme'] = $this->defaultScheme;
        }

        return $options;
    }
}
