<?php

namespace DeepCopy\Filter;

use ReflectionProperty;


class SetNullFilter implements Filter
{
    
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = new ReflectionProperty($object, $property);

        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, null);
    }
}
