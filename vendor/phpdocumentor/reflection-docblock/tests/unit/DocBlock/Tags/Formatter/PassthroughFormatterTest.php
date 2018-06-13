<?php


namespace phpDocumentor\Reflection\DocBlock\Tags\Formatter;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;


class PassthroughFormatterTest extends \PHPUnit_Framework_TestCase
{
    
    public function testFormatterCallsToStringAndReturnsAStandardRepresentation()
    {
        $expected = '@unknown-tag This is a description';

        $fixture = new PassthroughFormatter();

        $this->assertSame(
            $expected,
            $fixture->format(new Generic('unknown-tag', new Description('This is a description')))
        );
    }
}
