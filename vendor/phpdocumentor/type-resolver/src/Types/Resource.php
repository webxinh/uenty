<?php


namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;


final class Resource implements Type
{
    
    public function __toString()
    {
        return 'resource';
    }
}
