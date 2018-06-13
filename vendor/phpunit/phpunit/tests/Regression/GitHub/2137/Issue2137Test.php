<?php
class Issue2137Test extends PHPUnit_Framework_TestCase
{
    
    public function testBrandService($provided, $expected)
    {
        $this->assertSame($provided, $expected);
    }


    public function provideBrandService()
    {
        return [
            //[true, true]
            new stdClass() // not valid
        ];
    }


    
    public function testSomethingElseInvalid($provided, $expected)
    {
        $this->assertSame($provided, $expected);
    }
}
