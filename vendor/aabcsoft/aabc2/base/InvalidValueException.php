<?php


namespace aabc\base;


class InvalidValueException extends \UnexpectedValueException
{
    
    public function getName()
    {
        return 'Invalid Return Value';
    }
}
