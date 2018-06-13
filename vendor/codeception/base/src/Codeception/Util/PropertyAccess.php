<?php
namespace Codeception\Util;

class PropertyAccess
{
    
    public static function readPrivateProperty($object, $property, $class = null)
    {
        return ReflectionHelper::readPrivateProperty($object, $property, $class);
    }
}
