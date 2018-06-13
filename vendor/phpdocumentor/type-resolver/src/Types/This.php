<?php


namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;


final class This implements Type
{
    
    public function __toString()
    {
        return '$this';
    }
}
