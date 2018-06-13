<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Types\Context;


class LinkTest extends \PHPUnit_Framework_TestCase
{
    
    public function testIfCorrectTagNameIsReturned()
    {
        $fixture = new Link('http://this.is.my/link', new Description('Description'));

        $this->assertSame('link', $fixture->getName());
    }

    
    public function testIfTagCanBeRenderedUsingDefaultFormatter()
    {
        $fixture = new Link('http://this.is.my/link', new Description('Description'));

        $this->assertSame('@link http://this.is.my/link Description', $fixture->render());
    }

    
    public function testIfTagCanBeRenderedUsingSpecificFormatter()
    {
        $fixture = new Link('http://this.is.my/link', new Description('Description'));

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    
    public function testHasLinkUrl()
    {
        $expected = 'http://this.is.my/link';

        $fixture = new Link($expected);

        $this->assertSame($expected, $fixture->getLink());
    }

    
    public function testHasDescription()
    {
        $expected = new Description('Description');

        $fixture = new Link('http://this.is.my/link', $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    
    public function testStringRepresentationIsReturned()
    {
        $fixture = new Link('http://this.is.my/link', new Description('Description'));

        $this->assertSame('http://this.is.my/link Description', (string)$fixture);
    }

    
    public function testFactoryMethod()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $context = new Context('');

        $links = 'http://this.is.my/link';
        $description = new Description('My Description');

        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Link::create('http://this.is.my/link My Description', $descriptionFactory, $context);

        $this->assertSame('http://this.is.my/link My Description', (string)$fixture);
        $this->assertSame($links, $fixture->getLink());
        $this->assertSame($description, $fixture->getDescription());
    }

    
    public function testFactoryMethodCreatesEmptyLinkTag()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $descriptionFactory->shouldReceive('create')->never();

        $fixture = Link::create('', $descriptionFactory, new Context(''));

        $this->assertSame('', (string)$fixture);
        $this->assertSame('', $fixture->getLink());
        $this->assertSame(null, $fixture->getDescription());
    }

    
    public function testFactoryMethodFailsIfVersionIsNotString()
    {
        $this->assertNull(Link::create([]));
    }
}
