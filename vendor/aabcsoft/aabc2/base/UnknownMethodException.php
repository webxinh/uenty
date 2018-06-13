<?php


namespace aabc\base;


class UnknownMethodException extends \BadMethodCallException
{
    
    public function getName()
    {
        return 'Unknown Method';
    }
}
