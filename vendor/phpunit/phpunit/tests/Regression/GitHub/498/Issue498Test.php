<?php

class Issue498Test extends PHPUnit_Framework_TestCase
{
    
    public function shouldBeTrue($testData)
    {
        $this->assertTrue(true);
    }

    
    public function shouldBeFalse($testData)
    {
        $this->assertFalse(false);
    }

    public function shouldBeTrueDataProvider()
    {

        //throw new Exception("Can't create the data");
        return [
            [true],
            [false]
        ];
    }

    public function shouldBeFalseDataProvider()
    {
        throw new Exception("Can't create the data");

        return [
            [true],
            [false]
        ];
    }
}
