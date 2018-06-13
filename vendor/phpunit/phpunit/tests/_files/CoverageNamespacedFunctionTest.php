<?php
class CoverageNamespacedFunctionTest extends PHPUnit_Framework_TestCase
{
    
    public function testFunc()
    {
        foo\func();
    }
}
