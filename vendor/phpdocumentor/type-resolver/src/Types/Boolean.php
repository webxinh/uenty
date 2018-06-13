<?php


namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;


final class Boolean implements Type
{
    
    public function __toString()
    {
        return 'bool';
    }
}
