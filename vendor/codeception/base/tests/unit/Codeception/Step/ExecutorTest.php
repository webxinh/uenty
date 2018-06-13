<?php

class ExecutorTest extends \PHPUnit_Framework_TestCase
{
    
    public function testRun($returnValue)
    {
        $expected = $returnValue;

        $executor = new \Codeception\Step\Executor(function () use ($returnValue) {
            return $returnValue;
        });
        $actual = $executor->run();

        $this->assertEquals($expected, $actual);
    }

    
    public function valuesProvider()
    {
        return array(
            array(true),
            array(false),
        );
    }
}
