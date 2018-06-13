<?php
namespace Codeception\Util;


class ReflectionHelper
{
    
    public static function readPrivateProperty($object, $property, $class = null)
    {
        if (is_null($class)) {
            $class = $object;
        }

        $property = new \ReflectionProperty($class, $property);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    
    public static function invokePrivateMethod($object, $method, $args = [], $class = null)
    {
        if (is_null($class)) {
            $class = $object;
        }

        $method = new \ReflectionMethod($class, $method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }

    
    public static function getClassShortName($object)
    {
        $path = explode('\\', get_class($object));
        return array_pop($path);
    }
}
