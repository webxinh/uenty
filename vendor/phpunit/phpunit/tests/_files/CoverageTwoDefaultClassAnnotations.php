<?php


class CoverageTwoDefaultClassAnnotations
{
    
    public function testSomething()
    {
        $o = new Foo\CoveredClass;
        $o->publicMethod();
    }
}
