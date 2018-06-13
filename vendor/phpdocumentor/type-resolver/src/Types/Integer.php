<?php


namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;

final class Integer implements Type
{
    
    public function __toString()
    {
        return 'int';
    }
}
