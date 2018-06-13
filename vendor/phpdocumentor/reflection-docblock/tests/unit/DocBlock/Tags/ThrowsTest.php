<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\String_;


class ThrowsTest extends \PHPUnit_Framework_TestCase
{
    
    public function testIfCorrectTagNameIsReturned()
    {
        $fixture = new Throws(new String_(), new Description('Description'));

        $this->assertSame('throws', $fixture->getName());
    }

    
    public function testIfTagCanBeRenderedUsingDefaultFormatter()
    {
        $fixture = new Throws(new String_(), new Description('Description'));

        $this->assertSame('@throws string Description', $fixture->render());
    }

    
    public function testIfTagCanBeRenderedUsingSpecificFormatter()
    {
        $fixture = new Throws(new String_(), new Description('Description'));

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    
    public function testHasType()
    {
        $expected = new String_();

        $fixture = new Throws($expected);

        $this->assertSame($expected, $fixture->getType());
    }

    
    public function testHasDescription()
    {
        $expected = new Description('Description');

        $fixture = new Throws(new String_(), $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    
    public function testStringRepresentationIsReturned()
    {
        $fixture = new Throws(new String_(), new Description('Description'));

        $this->assertSame('string Description', (string)$fixture);
    }

    
    public function testFactoryMethod()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = new TypeResolver();
        $context            = new Context('');

        $type        = new String_();
        $description = new Description('My Description');
        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Throws::create('string My Description', $resolver, $descriptionFactory, $context);

        $this->assertSame('string My Description', (string)$fixture);
        $this->assertEquals($type, $fixture->getType());
        $this->assertSame($description, $fixture->getDescription());
    }

    
    public function testFactoryMethodFailsIfBodyIsNotString()
    {
        $this->assertNull(Throws::create([]));
    }

    
    public function testFactoryMethodFailsIfBodyIsNotEmpty()
    {
        $this->assertNull(Throws::create(''));
    }

    
    public function testFactoryMethodFailsIfResolverIsNull()
    {
        Throws::create('body');
    }

    
    public function testFactoryMethodFailsIfDescriptionFactoryIsNull()
    {
        Throws::create('body', new TypeResolver());
    }
}
