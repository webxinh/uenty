<?php

namespace DeepCopy\Reflection;

class ReflectionHelper
{
    
    public static function getProperties(\ReflectionClass $ref)
    {
        $props = $ref->getProperties();
        $propsArr = array();

        foreach ($props as $prop) {
            $propertyName = $prop->getName();
            $propsArr[$propertyName] = $prop;
        }

        if ($parentClass = $ref->getParentClass()) {
            $parentPropsArr = self::getProperties($parentClass);
            foreach ($propsArr as $key => $property) {
                $parentPropsArr[$key] = $property;
            }

            return $parentPropsArr;
        }

        return $propsArr;
    }
}
