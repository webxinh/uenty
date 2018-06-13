<?php
class Issue2158Test extends PHPUnit_Framework_TestCase
{
    
    public function testSomething()
    {
        include(__DIR__ . '/constant.inc');
        $this->assertTrue(true);
    }

    
    public function testSomethingElse()
    {
        $this->assertTrue(defined('TEST_CONSTANT'));
    }
}
