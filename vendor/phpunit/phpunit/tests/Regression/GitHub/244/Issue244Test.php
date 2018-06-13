<?php
class Issue244Test extends PHPUnit_Framework_TestCase
{
    
    public function testWorks()
    {
        throw new Issue244Exception;
    }

    
    public function testFails()
    {
        throw new Issue244Exception;
    }

    
    public function testFailsTooIfExpectationIsANumber()
    {
        throw new Issue244Exception;
    }

    
    public function testFailsTooIfExceptionCodeIsANumber()
    {
        throw new Issue244ExceptionIntCode;
    }
}

class Issue244Exception extends Exception
{
    public function __construct()
    {
        $this->code = '123StringCode';
    }
}

class Issue244ExceptionIntCode extends Exception
{
    public function __construct()
    {
        $this->code = 123;
    }
}
