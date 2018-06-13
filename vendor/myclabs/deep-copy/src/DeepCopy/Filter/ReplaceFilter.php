<?php

namespace DeepCopy\Filter;


class ReplaceFilter implements Filter
{
    
    protected $callback;

    
    public function __construct(callable $callable)
    {
        $this->callback = $callable;
    }

    
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = new \ReflectionProperty($object, $property);
        $reflectionProperty->setAccessible(true);

        $value = call_user_func($this->callback, $reflectionProperty->getValue($object));

        $reflectionProperty->setValue($object, $value);
    }
}
