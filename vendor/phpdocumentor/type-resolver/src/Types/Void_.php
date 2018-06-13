<?php


namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;


final class Void_ implements Type
{
    
    public function __toString()
    {
        return 'void';
    }
}
