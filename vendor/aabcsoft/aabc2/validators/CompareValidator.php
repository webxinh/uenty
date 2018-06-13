<?php


namespace aabc\validators;

use Aabc;
use aabc\base\InvalidConfigException;
use aabc\helpers\Html;


class CompareValidator extends Validator
{
    
    const TYPE_STRING = 'string';
    
    const TYPE_NUMBER = 'number';

    
    public $compareAttribute;
    
    public $compareValue;
    
    public $type = self::TYPE_STRING;
    
    public $operator = '==';
    
    public $message;


    
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            switch ($this->operator) {
                case '==':
                    $this->message = Aabc::t('aabc', '{attribute} must be equal to "{compareValueOrAttribute}".');
                    break;
                case '===':
                    $this->message = Aabc::t('aabc', '{attribute} must be equal to "{compareValueOrAttribute}".');
                    break;
                case '!=':
                    $this->message = Aabc::t('aabc', '{attribute} must not be equal to "{compareValueOrAttribute}".');
                    break;
                case '!==':
                    $this->message = Aabc::t('aabc', '{attribute} must not be equal to "{compareValueOrAttribute}".');
                    break;
                case '>':
                    $this->message = Aabc::t('aabc', '{attribute} must be greater than "{compareValueOrAttribute}".');
                    break;
                case '>=':
                    $this->message = Aabc::t('aabc', '{attribute} must be greater than or equal to "{compareValueOrAttribute}".');
                    break;
                case '<':
                    $this->message = Aabc::t('aabc', '{attribute} must be less than "{compareValueOrAttribute}".');
                    break;
                case '<=':
                    $this->message = Aabc::t('aabc', '{attribute} must be less than or equal to "{compareValueOrAttribute}".');
                    break;
                default:
                    throw new InvalidConfigException("Unknown operator: {$this->operator}");
            }
        }
    }

    
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (is_array($value)) {
            $this->addError($model, $attribute, Aabc::t('aabc', '{attribute} is invalid.'));

            return;
        }
        if ($this->compareValue !== null) {
            $compareLabel = $compareValue = $compareValueOrAttribute = $this->compareValue;
        } else {
            $compareAttribute = $this->compareAttribute === null ? $attribute . '_repeat' : $this->compareAttribute;
            $compareValue = $model->$compareAttribute;
            $compareLabel = $compareValueOrAttribute = $model->getAttributeLabel($compareAttribute);
        }

        if (!$this->compareValues($this->operator, $this->type, $value, $compareValue)) {
            $this->addError($model, $attribute, $this->message, [
                'compareAttribute' => $compareLabel,
                'compareValue' => $compareValue,
                'compareValueOrAttribute' => $compareValueOrAttribute,
            ]);
        }
    }

    
    protected function validateValue($value)
    {
        if ($this->compareValue === null) {
            throw new InvalidConfigException('CompareValidator::compareValue must be set.');
        }
        if (!$this->compareValues($this->operator, $this->type, $value, $this->compareValue)) {
            return [$this->message, [
                'compareAttribute' => $this->compareValue,
                'compareValue' => $this->compareValue,
                'compareValueOrAttribute' => $this->compareValue,
            ]];
        } else {
            return null;
        }
    }

    
    protected function compareValues($operator, $type, $value, $compareValue)
    {
        if ($type === self::TYPE_NUMBER) {
            $value = (float) $value;
            $compareValue = (float) $compareValue;
        } else {
            $value = (string) $value;
            $compareValue = (string) $compareValue;
        }
        switch ($operator) {
            case '==':
                return $value == $compareValue;
            case '===':
                return $value === $compareValue;
            case '!=':
                return $value != $compareValue;
            case '!==':
                return $value !== $compareValue;
            case '>':
                return $value > $compareValue;
            case '>=':
                return $value >= $compareValue;
            case '<':
                return $value < $compareValue;
            case '<=':
                return $value <= $compareValue;
            default:
                return false;
        }
    }

    
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'aabc.validation.compare(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

    
    public function getClientOptions($model, $attribute)
    {
        $options = [
            'operator' => $this->operator,
            'type' => $this->type,
        ];

        if ($this->compareValue !== null) {
            $options['compareValue'] = $this->compareValue;
            $compareLabel = $compareValue = $compareValueOrAttribute = $this->compareValue;
        } else {
            $compareAttribute = $this->compareAttribute === null ? $attribute . '_repeat' : $this->compareAttribute;
            $compareValue = $model->getAttributeLabel($compareAttribute);
            $options['compareAttribute'] = Html::getInputId($model, $compareAttribute);
            $compareLabel = $compareValueOrAttribute = $model->getAttributeLabel($compareAttribute);
        }

        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        $options['message'] = Aabc::$app->getI18n()->format($this->message, [
            'attribute' => $model->getAttributeLabel($attribute),
            'compareAttribute' => $compareLabel,
            'compareValue' => $compareValue,
            'compareValueOrAttribute' => $compareValueOrAttribute,
        ], Aabc::$app->language);

        return $options;
    }
}
