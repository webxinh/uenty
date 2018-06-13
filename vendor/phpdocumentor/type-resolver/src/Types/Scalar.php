<?php


namespace phpDocumentor\Reflection\Types;

use phpDocumentor\Reflection\Type;


final class Scalar implements Type
{
    
    public function __toString()
    {
        return 'scalar';
    }
}
