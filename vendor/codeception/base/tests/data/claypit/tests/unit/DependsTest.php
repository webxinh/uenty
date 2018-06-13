<?php
class DependsTest extends \Codeception\Test\Unit {

    
    public function testTwo($res)
    {
        $this->assertTrue(true);
        $this->assertEquals(1, $res);
    }
    
    
    public function testThree()
    {
        $this->assertTrue(true);
    }

    public function testFour()
    {
        $this->assertTrue(true);        
    }
    
    
    
    public function testOne()
    {
        $this->assertTrue(false);
        return 1;
    }

}