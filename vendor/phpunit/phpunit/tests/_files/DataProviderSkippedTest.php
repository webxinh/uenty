<?php
class DataProviderSkippedTest extends PHPUnit_Framework_TestCase
{
    
    public function testSkipped($a, $b, $c)
    {
        $this->assertTrue(true);
    }

    
    public function testAdd($a, $b, $c)
    {
        $this->assertEquals($c, $a + $b);
    }

    public function skippedTestProviderMethod()
    {
        $this->markTestSkipped('skipped');

        return [
          [0, 0, 0],
          [0, 1, 1],
        ];
    }

    public static function providerMethod()
    {
        return [
          [0, 0, 0],
          [0, 1, 1],
        ];
    }
}
