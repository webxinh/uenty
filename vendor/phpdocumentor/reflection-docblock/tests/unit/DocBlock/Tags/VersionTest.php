<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Types\Context;


class VersionTest extends \PHPUnit_Framework_TestCase
{
    
    public function testIfCorrectTagNameIsReturned()
    {
        $fixture = new Version('1.0', new Description('Description'));

        $this->assertSame('version', $fixture->getName());
    }

    
    public function testIfTagCanBeRenderedUsingDefaultFormatter()
    {
        $fixture = new Version('1.0', new Description('Description'));

        $this->assertSame('@version 1.0 Description', $fixture->render());
    }

    
    public function testIfTagCanBeRenderedUsingSpecificFormatter()
    {
        $fixture = new Version('1.0', new Description('Description'));

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    
    public function testHasVersionNumber()
    {
        $expected = '1.0';

        $fixture = new Version($expected);

        $this->assertSame($expected, $fixture->getVersion());
    }

    
    public function testHasDescription()
    {
        $expected = new Description('Description');

        $fixture = new Version('1.0', $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    
    public function testStringRepresentationIsReturned()
    {
        $fixture = new Version('1.0', new Description('Description'));

        $this->assertSame('1.0 Description', (string)$fixture);
    }

    
    public function testFactoryMethod()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $context            = new Context('');

        $version     = '1.0';
        $description = new Description('My Description');

        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Version::create('1.0 My Description', $descriptionFactory, $context);

        $this->assertSame('1.0 My Description', (string)$fixture);
        $this->assertSame($version, $fixture->getVersion());
        $this->assertSame($description, $fixture->getDescription());
    }

    
    public function testFactoryMethodCreatesEmptyVersionTag()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $descriptionFactory->shouldReceive('create')->never();

        $fixture = Version::create('', $descriptionFactory, new Context(''));

        $this->assertSame('', (string)$fixture);
        $this->assertSame(null, $fixture->getVersion());
        $this->assertSame(null, $fixture->getDescription());
    }

    
    public function testFactoryMethodFailsIfVersionIsNotString()
    {
        $this->assertNull(Version::create([]));
    }

    
    public function testFactoryMethodReturnsNullIfBodyDoesNotMatchRegex()
    {
        $this->assertNull(Version::create('dkhf<'));
    }
}
