<?php


namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;


final class Array_ implements Type
{
    
    private $valueType;

    
    private $keyType;

    
    public function __construct(Type $valueType = null, Type $keyType = null)
    {
        if ($keyType === null) {
            $keyType = new Compound([ new String_(), new Integer() ]);
        }
        if ($valueType === null) {
            $valueType = new Mixed();
        }

        $this->valueType = $valueType;
        $this->keyType = $keyType;
    }

    
    public function getKeyType()
    {
        return $this->keyType;
    }

    
    public function getValueType()
    {
        return $this->valueType;
    }

    
    public function __toString()
    {
        if ($this->valueType instanceof Mixed) {
            return 'array';
        }

        return $this->valueType . '[]';
    }
}
