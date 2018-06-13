<?php 

class ExceptionTest extends PHPUnit_Framework_TestCase
{

    
    public function testError()
    {
        throw new \RuntimeException('Helllo!');
    }
} 