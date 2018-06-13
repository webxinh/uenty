<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Types\Context;


class SinceTest extends \PHPUnit_Framework_TestCase
{
    
    public function testIfCorrectTagNameIsReturned()
    {
        $fixture = new Since('1.0', new Description('Description'));

        $this->assertSame('since', $fixture->getName());
    }

    
    public function testIfTagCanBeRenderedUsingDefaultFormatter()
    {
        $fixture = new Since('1.0', new Description('Description'));

        $this->assertSame('@since 1.0 Description', $fixture->render());
    }

    
    public function testIfTagCanBeRenderedUsingSpecificFormatter()
    {
        $fixture = new Since('1.0', new Description('Description'));

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    
    public function testHasVersionNumber()
    {
        $expected = '1.0';

        $fixture = new Since($expected);

        $this->assertSame($expected, $fixture->getVersion());
    }

    
    public function testHasDescription()
    {
        $expected = new Description('Description');

        $fixture = new Since('1.0', $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    
    public function testStringRepresentationIsReturned()
    {
        $fixture = new Since('1.0', new Description('Description'));

        $this->assertSame('1.0 Description', (string)$fixture);
    }

    
    public function testFactoryMethod()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $context            = new Context('');

        $version     = '1.0';
        $description = new Description('My Description');

        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Since::create('1.0 My Description', $descriptionFactory, $context);

        $this->assertSame('1.0 My Description', (string)$fixture);
        $this->assertSame($version, $fixture->getVersion());
        $this->assertSame($description, $fixture->getDescription());
    }

    
    public function testFactoryMethodCreatesEmptySinceTag()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $descriptionFactory->shouldReceive('create')->never();

        $fixture = Since::create('', $descriptionFactory, new Context(''));

        $this->assertSame('', (string)$fixture);
        $this->assertSame(null, $fixture->getVersion());
        $this->assertSame(null, $fixture->getDescription());
    }

    
    public function testFactoryMethodFailsIfSinceIsNotString()
    {
        $this->assertNull(Since::create([]));
    }

    
    public function testFactoryMethodReturnsNullIfBodyDoesNotMatchRegex()
    {
        $this->assertNull(Since::create('dkhf<'));
    }
}
