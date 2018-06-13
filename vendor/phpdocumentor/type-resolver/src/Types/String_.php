<?php


namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;


final class String_ implements Type
{
    
    public function __toString()
    {
        return 'string';
    }
}
