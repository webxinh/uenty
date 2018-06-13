<?php
class MultiDependencyTest extends PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        return 'foo';
    }

    public function testTwo()
    {
        return 'bar';
    }

    
    public function testThree($a, $b)
    {
        $this->assertEquals('foo', $a);
        $this->assertEquals('bar', $b);
    }
}
