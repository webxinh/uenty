<?php


namespace aabc\validators;

use Aabc;
use aabc\base\Component;
use aabc\base\NotSupportedException;


class Validator extends Component
{
    
    public static $builtInValidators = [
        'boolean' => 'aabc\validators\BooleanValidator',
        'captcha' => 'aabc\captcha\CaptchaValidator',
        'compare' => 'aabc\validators\CompareValidator',
        'date' => 'aabc\validators\DateValidator',
        'datetime' => [
            'class' => 'aabc\validators\DateValidator',
            'type' => DateValidator::TYPE_DATETIME,
        ],
        'time' => [
            'class' => 'aabc\validators\DateValidator',
            'type' => DateValidator::TYPE_TIME,
        ],
        'default' => 'aabc\validators\DefaultValueValidator',
        'double' => 'aabc\validators\NumberValidator',
        'each' => 'aabc\validators\EachValidator',
        'email' => 'aabc\validators\EmailValidator',
        'exist' => 'aabc\validators\ExistValidator',
        'file' => 'aabc\validators\FileValidator',
        'filter' => 'aabc\validators\FilterValidator',
        'image' => 'aabc\validators\ImageValidator',
        'in' => 'aabc\validators\RangeValidator',
        'integer' => [
            'class' => 'aabc\validators\NumberValidator',
            'integerOnly' => true,
        ],
        'match' => 'aabc\validators\RegularExpressionValidator',
        'number' => 'aabc\validators\NumberValidator',
        'required' => 'aabc\validators\RequiredValidator',
        'safe' => 'aabc\validators\SafeValidator',
        'string' => 'aabc\validators\StringValidator',
        'trim' => [
            'class' => 'aabc\validators\FilterValidator',
            'filter' => 'trim',
            'skipOnArray' => true,
        ],
        'unique' => 'aabc\validators\UniqueValidator',
        'url' => 'aabc\validators\UrlValidator',
        'ip' => 'aabc\validators\IpValidator',
    ];
    
    public $attributes = [];
    
    public $message;
    
    public $on = [];
    
    public $except = [];
    
    public $skipOnError = true;
    
    public $skipOnEmpty = true;
    
    public $enableClientValidation = true;
    
    public $isEmpty;
    
    public $when;
    
    public $whenClient;


    
    public static function createValidator($type, $model, $attributes, $params = [])
    {
        $params['attributes'] = $attributes;

        if ($type instanceof \Closure || $model->hasMethod($type)) {
            // method-based validator
            $params['class'] = __NAMESPACE__ . '\InlineValidator';
            $params['method'] = $type;
        } else {
            if (isset(static::$builtInValidators[$type])) {
                $type = static::$builtInValidators[$type];
            }
            if (is_array($type)) {
                $params = array_merge($type, $params);
            } else {
                $params['class'] = $type;
            }
        }

        return Aabc::createObject($params);
    }

    
    public function init()
    {
        parent::init();
        $this->attributes = (array) $this->attributes;
        $this->on = (array) $this->on;
        $this->except = (array) $this->except;
    }

    
    public function validateAttributes($model, $attributes = null)
    {
        if (is_array($attributes)) {
            $newAttributes = [];
            foreach ($attributes as $attribute) {
                if (in_array($attribute, $this->attributes) || in_array('!' . $attribute, $this->attributes)) {
                    $newAttributes[] = $attribute;
                }
            }
            $attributes = $newAttributes;
        } else {
            $attributes = [];
            foreach ($this->attributes as $attribute) {
                $attributes[] = $attribute[0] === '!' ? substr($attribute, 1) : $attribute;
            }
        }

        foreach ($attributes as $attribute) {
            $skip = $this->skipOnError && $model->hasErrors($attribute)
                || $this->skipOnEmpty && $this->isEmpty($model->$attribute);
            if (!$skip) {
                if ($this->when === null || call_user_func($this->when, $model, $attribute)) {
                    $this->validateAttribute($model, $attribute);
                }
            }
        }
    }

    
    public function validateAttribute($model, $attribute)
    {
        $result = $this->validateValue($model->$attribute);
        if (!empty($result)) {
            $this->addError($model, $attribute, $result[0], $result[1]);
        }
    }

    
    public function validate($value, &$error = null)
    {
        $result = $this->validateValue($value);
        if (empty($result)) {
            return true;
        }

        list($message, $params) = $result;
        $params['attribute'] = Aabc::t('aabc', 'the input value');
        if (is_array($value)) {
            $params['value'] = 'array()';
        } elseif (is_object($value)) {
            $params['value'] = 'object';
        } else {
            $params['value'] = $value;
        }
        $error = Aabc::$app->getI18n()->format($message, $params, Aabc::$app->language);

        return false;
    }

    
    protected function validateValue($value)
    {
        throw new NotSupportedException(get_class($this) . ' does not support validateValue().');
    }

    
    public function clientValidateAttribute($model, $attribute, $view)
    {
        return null;
    }

    
    public function getClientOptions($model, $attribute)
    {
        return [];
    }

    
    public function isActive($scenario)
    {
        return !in_array($scenario, $this->except, true) && (empty($this->on) || in_array($scenario, $this->on, true));
    }

    
    public function addError($model, $attribute, $message, $params = [])
    {
        $params['attribute'] = $model->getAttributeLabel($attribute);
        if (!isset($params['value'])) {
            $value = $model->$attribute;
            if (is_array($value)) {
                $params['value'] = 'array()';
            } elseif (is_object($value) && !method_exists($value, '__toString')) {
                $params['value'] = '(object)';
            } else {
                $params['value'] = $value;
            }
        }
        $model->addError($attribute, Aabc::$app->getI18n()->format($message, $params, Aabc::$app->language));
    }

    
    public function isEmpty($value)
    {
        if ($this->isEmpty !== null) {
            return call_user_func($this->isEmpty, $value);
        } else {
            return $value === null || $value === [] || $value === '';
        }
    }
}
