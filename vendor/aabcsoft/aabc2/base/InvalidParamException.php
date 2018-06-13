<?php


namespace aabc\base;


class InvalidParamException extends \BadMethodCallException
{
    
    public function getName()
    {
        return 'Invalid Parameter';
    }
}
