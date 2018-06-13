<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\String_;


class SourceTest extends \PHPUnit_Framework_TestCase
{
    
    public function testIfCorrectTagNameIsReturned()
    {
        $fixture = new Source(1, null, new Description('Description'));

        $this->assertSame('source', $fixture->getName());
    }

    
    public function testIfTagCanBeRenderedUsingDefaultFormatter()
    {
        $fixture = new Source(1, 10, new Description('Description'));
        $this->assertSame('@source 1 10 Description', $fixture->render());

        $fixture = new Source(1, null, new Description('Description'));
        $this->assertSame('@source 1 Description', $fixture->render());

        $fixture = new Source(1);
        $this->assertSame('@source 1', $fixture->render());
    }

    
    public function testIfTagCanBeRenderedUsingSpecificFormatter()
    {
        $fixture = new Source(1);

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    
    public function testHasStartingLine()
    {
        $expected = 1;

        $fixture = new Source($expected);

        $this->assertSame($expected, $fixture->getStartingLine());
    }

    
    public function testHasLineCount()
    {
        $expected = 2;

        $fixture = new Source(1, $expected);

        $this->assertSame($expected, $fixture->getLineCount());
    }

    
    public function testHasDescription()
    {
        $expected = new Description('Description');

        $fixture = new Source('1', null, $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    
    public function testStringRepresentationIsReturned()
    {
        $fixture = new Source(1, 10, new Description('Description'));

        $this->assertSame('1 10 Description', (string)$fixture);
    }

    
    public function testFactoryMethod()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $context            = new Context('');

        $description = new Description('My Description');
        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Source::create('1 10 My Description', $descriptionFactory, $context);

        $this->assertSame('1 10 My Description', (string)$fixture);
        $this->assertSame(1, $fixture->getStartingLine());
        $this->assertSame(10, $fixture->getLineCount());
        $this->assertSame($description, $fixture->getDescription());
    }

    
    public function testFactoryMethodFailsIfEmptyBodyIsGiven()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        Source::create('', $descriptionFactory);
    }

    
    public function testFactoryMethodFailsIfBodyIsNotString()
    {
        Source::create([]);
    }

    
    public function testFactoryMethodFailsIfDescriptionFactoryIsNull()
    {
        Source::create('1');
    }

    
    public function testExceptionIsThrownIfStartingLineIsNotInteger()
    {
        new Source('blabla');
    }

    
    public function testExceptionIsThrownIfLineCountIsNotIntegerOrNull()
    {
        new Source('1', []);
    }
}
