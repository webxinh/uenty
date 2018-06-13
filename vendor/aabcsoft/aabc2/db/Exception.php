<?php


namespace aabc\db;


class Exception extends \aabc\base\Exception
{
    
    public $errorInfo = [];


    
    public function __construct($message, $errorInfo = [], $code = 0, \Exception $previous = null)
    {
        $this->errorInfo = $errorInfo;
        parent::__construct($message, $code, $previous);
    }

    
    public function getName()
    {
        return 'Database Exception';
    }

    
    public function __toString()
    {
        return parent::__toString() . PHP_EOL
        . 'Additional Information:' . PHP_EOL . print_r($this->errorInfo, true);
    }
}
