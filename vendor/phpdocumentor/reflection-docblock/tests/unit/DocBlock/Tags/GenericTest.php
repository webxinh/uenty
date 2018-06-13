<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Types\Context;


class GenericTest extends \PHPUnit_Framework_TestCase
{
    
    public function testIfCorrectTagNameIsReturned()
    {
        $fixture = new Generic('generic', new Description('Description'));

        $this->assertSame('generic', $fixture->getName());
    }

    
    public function testIfTagCanBeRenderedUsingDefaultFormatter()
    {
        $fixture = new Generic('generic', new Description('Description'));

        $this->assertSame('@generic Description', $fixture->render());
    }

    
    public function testIfTagCanBeRenderedUsingSpecificFormatter()
    {
        $fixture = new Generic('generic', new Description('Description'));

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    
    public function testHasDescription()
    {
        $expected = new Description('Description');

        $fixture = new Generic('generic', $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    
    public function testStringRepresentationIsReturned()
    {
        $fixture = new Generic('generic', new Description('Description'));

        $this->assertSame('Description', (string)$fixture);
    }

    
    public function testFactoryMethod()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $context = new Context('');

        $generics = 'generic';
        $description = new Description('My Description');

        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Generic::create('My Description', 'generic', $descriptionFactory, $context);

        $this->assertSame('My Description', (string)$fixture);
        $this->assertSame($generics, $fixture->getName());
        $this->assertSame($description, $fixture->getDescription());
    }

    
    public function testFactoryMethodFailsIfNameIsNotString()
    {
        Generic::create('', []);
    }

    
    public function testFactoryMethodFailsIfNameIsNotEmpty()
    {
        Generic::create('', '');
    }

    
    public function testFactoryMethodFailsIfNameContainsIllegalCharacters()
    {
        Generic::create('', 'name/myname');
    }
}
