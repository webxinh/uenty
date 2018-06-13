<?php


namespace aabc\console;

use aabc\base\UserException;


class Exception extends UserException
{
    
    public function getName()
    {
        return 'Error';
    }
}
