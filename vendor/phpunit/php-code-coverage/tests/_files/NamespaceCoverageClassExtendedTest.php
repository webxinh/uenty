<?php
class NamespaceCoverageClassExtendedTest extends PHPUnit_Framework_TestCase
{
    
    public function testSomething()
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
