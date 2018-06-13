<?php


namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;


final class Static_ implements Type
{
    
    public function __toString()
    {
        return 'static';
    }
}
