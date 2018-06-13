<?php


namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;


final class Self_ implements Type
{
    
    public function __toString()
    {
        return 'self';
    }
}
