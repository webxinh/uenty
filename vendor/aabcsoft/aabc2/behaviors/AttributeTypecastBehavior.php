<?php


namespace aabc\behaviors;

use aabc\base\Behavior;
use aabc\base\InvalidParamException;
use aabc\base\Model;
use aabc\db\BaseActiveRecord;
use aabc\validators\BooleanValidator;
use aabc\validators\NumberValidator;
use aabc\validators\StringValidator;


class AttributeTypecastBehavior extends Behavior
{
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_STRING = 'string';

    
    public $owner;
    
    public $attributeTypes;
    
    public $skipOnNull = true;
    
    public $typecastAfterValidate = true;
    
    public $typecastBeforeSave = false;
    
    public $typecastAfterFind = false;

    
    private static $autoDetectedAttributeTypes = [];


    
    public static function clearAutoDetectedAttributeTypes()
    {
        self::$autoDetectedAttributeTypes = [];
    }

    
    public function attach($owner)
    {
        parent::attach($owner);

        if ($this->attributeTypes === null) {
            $ownerClass = get_class($this->owner);
            if (!isset(self::$autoDetectedAttributeTypes[$ownerClass])) {
                self::$autoDetectedAttributeTypes[$ownerClass] = $this->detectAttributeTypes();
            }
            $this->attributeTypes = self::$autoDetectedAttributeTypes[$ownerClass];
        }
    }

    
    public function typecastAttributes($attributeNames = null)
    {
        $attributeTypes = [];

        if ($attributeNames === null) {
            $attributeTypes = $this->attributeTypes;
        } else {
            foreach ($attributeNames as $attribute) {
                if (!isset($this->attributeTypes[$attribute])) {
                    throw new InvalidParamException("There is no type mapping for '{$attribute}'.");
                }
                $attributeTypes[$attribute] = $this->attributeTypes[$attribute];
            }
        }

        foreach ($attributeTypes as $attribute => $type) {
            $value = $this->owner->{$attribute};
            if ($this->skipOnNull && $value === null) {
                continue;
            }
            $this->owner->{$attribute} = $this->typecastValue($value, $type);
        }
    }

    
    protected function typecastValue($value, $type)
    {
        if (is_scalar($type)) {
            if (is_object($value) && method_exists($value, '__toString')) {
                $value = $value->__toString();
            }

            switch ($type) {
                case self::TYPE_INTEGER:
                    return (int) $value;
                case self::TYPE_FLOAT:
                    return (float) $value;
                case self::TYPE_BOOLEAN:
                    return (bool) $value;
                case self::TYPE_STRING:
                    return (string) $value;
                default:
                    throw new InvalidParamException("Unsupported type '{$type}'");
            }
        }

        return call_user_func($type, $value);
    }

    
    protected function detectAttributeTypes()
    {
        $attributeTypes = [];
        foreach ($this->owner->getValidators() as $validator) {
            $type = null;
            if ($validator instanceof BooleanValidator) {
                $type = self::TYPE_BOOLEAN;
            } elseif ($validator instanceof NumberValidator) {
                $type = $validator->integerOnly ? self::TYPE_INTEGER : self::TYPE_FLOAT;
            } elseif ($validator instanceof StringValidator) {
                $type = self::TYPE_STRING;
            }

            if ($type !== null) {
                foreach ((array)$validator->attributes as $attribute) {
                    $attributeTypes[$attribute] = $type;
                }
            }
        }
        return $attributeTypes;
    }

    
    public function events()
    {
        $events = [];

        if ($this->typecastAfterValidate) {
            $events[Model::EVENT_AFTER_VALIDATE] = 'afterValidate';
        }
        if ($this->typecastBeforeSave) {
            $events[BaseActiveRecord::EVENT_BEFORE_INSERT] = 'beforeSave';
            $events[BaseActiveRecord::EVENT_BEFORE_UPDATE] = 'beforeSave';
        }
        if ($this->typecastAfterFind) {
            $events[BaseActiveRecord::EVENT_AFTER_FIND] = 'afterFind';
        }

        return $events;
    }

    
    public function afterValidate($event)
    {
        if (!$this->owner->hasErrors()) {
            $this->typecastAttributes();
        }
    }

    
    public function beforeSave($event)
    {
        $this->typecastAttributes();
    }

    
    public function afterFind($event)
    {
        $this->typecastAttributes();
    }
}
