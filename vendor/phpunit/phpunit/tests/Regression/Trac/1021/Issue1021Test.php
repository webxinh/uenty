<?php
class Issue1021Test extends PHPUnit_Framework_TestCase
{
    
    public function testSomething($data)
    {
        $this->assertTrue($data);
    }

    
    public function testSomethingElse()
    {
    }

    public function provider()
    {
        return [[true]];
    }
}
