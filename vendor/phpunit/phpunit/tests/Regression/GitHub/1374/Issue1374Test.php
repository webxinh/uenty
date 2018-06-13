<?php

class Issue1374Test extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        print __FUNCTION__;
    }

    public function testSomething()
    {
        $this->fail('This should not be reached');
    }

    protected function tearDown()
    {
        print __FUNCTION__;
    }
}
