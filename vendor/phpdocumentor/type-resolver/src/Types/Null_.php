<?php


namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;


final class Null_ implements Type
{
    
    public function __toString()
    {
        return 'null';
    }
}
