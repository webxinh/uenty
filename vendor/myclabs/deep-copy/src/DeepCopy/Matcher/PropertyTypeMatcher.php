<?php

namespace DeepCopy\Matcher;

use ReflectionProperty;


class PropertyTypeMatcher implements Matcher
{
    
    private $propertyType;

    
    public function __construct($propertyType)
    {
        $this->propertyType = $propertyType;
    }

    
    public function matches($object, $property)
    {
        $reflectionProperty = new ReflectionProperty($object, $property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object) instanceof $this->propertyType;
    }
}
