<?php


namespace aabc\di;

use \aabc\base\InvalidConfigException;


class NotInstantiableException extends InvalidConfigException
{
    
    public function __construct($class, $message = null, $code = 0, \Exception $previous = null)
    {
        if ($message === null) {
            $message = "Can not instantiate $class.";
        }
        parent::__construct($message, $code, $previous);
    }

    
    public function getName()
    {
        return 'Not instantiable';
    }
}
