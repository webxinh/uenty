<?php


namespace phpDocumentor\Reflection\DocBlock;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;


class DescriptionTest extends \PHPUnit_Framework_TestCase
{
    
    public function testDescriptionCanRenderUsingABodyWithPlaceholdersAndTags()
    {
        $body = 'This is a %1$s body.';
        $expected = 'This is a {@internal significant } body.';
        $tags = [new Generic('internal', new Description('significant '))];

        $fixture = new Description($body, $tags);

        // without formatter (thus the PassthroughFormatter by default)
        $this->assertSame($expected, $fixture->render());

        // with a custom formatter
        $formatter = m::mock(PassthroughFormatter::class);
        $formatter->shouldReceive('format')->with($tags[0])->andReturn('@internal significant ');
        $this->assertSame($expected, $fixture->render($formatter));
    }

    
    public function testDescriptionCanBeCastToString()
    {
        $body = 'This is a %1$s body.';
        $expected = 'This is a {@internal significant } body.';
        $tags = [new Generic('internal', new Description('significant '))];

        $fixture = new Description($body, $tags);

        $this->assertSame($expected, (string)$fixture);
    }

    
    public function testBodyTemplateMustBeAString()
    {
        new Description([]);
    }
}
