<?php


namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;


final class Compound implements Type
{
    
    private $types = [];

    
    public function __construct(array $types)
    {
        foreach ($types as $type) {
            if (!$type instanceof Type) {
                throw new \InvalidArgumentException('A compound type can only have other types as elements');
            }
        }

        $this->types = $types;
    }

    
    public function get($index)
    {
        if (!$this->has($index)) {
            return null;
        }

        return $this->types[$index];
    }

    
    public function has($index)
    {
        return isset($this->types[$index]);
    }

    
    public function __toString()
    {
        return implode('|', $this->types);
    }
}
