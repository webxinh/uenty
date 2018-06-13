<?php
class SomeErrorClass {


    public function some_method()
    {
        $a = [];

        $a .= 'test';

    }

}


class ErrorTest extends \Codeception\Test\Unit
{

    
    protected $tester;

    
    function testGetError()
    {

        $test = new SomeErrorClass;

        $test->some_method();

    }

}