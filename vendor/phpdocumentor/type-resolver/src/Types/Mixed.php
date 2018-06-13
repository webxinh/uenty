<?php


namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;


final class Mixed implements Type
{
    
    public function __toString()
    {
        return 'mixed';
    }
}
