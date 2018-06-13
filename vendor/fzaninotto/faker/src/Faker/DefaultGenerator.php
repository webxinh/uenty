<?php

namespace Faker;


class DefaultGenerator
{
    protected $default;

    public function __construct($default = null)
    {
        $this->default = $default;
    }

    
    public function __get($attribute)
    {
        return $this->default;
    }

    
    public function __call($method, $attributes)
    {
        return $this->default;
    }
}
