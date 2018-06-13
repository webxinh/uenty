<?php

class DataProviderDependencyTest extends PHPUnit_Framework_TestCase
{
    public function testReference()
    {
        $this->markTestSkipped('This test should be skipped.');
        $this->assertTrue(true);
    }

    
    public function testDependency($param)
    {
    }

    public function provider()
    {
        $this->markTestSkipped('Any test with this data provider should be skipped.');
        return [];
    }
}
