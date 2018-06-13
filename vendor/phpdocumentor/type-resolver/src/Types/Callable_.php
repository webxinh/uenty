<?php


namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;


final class Callable_ implements Type
{
    
    public function __toString()
    {
        return 'callable';
    }
}
