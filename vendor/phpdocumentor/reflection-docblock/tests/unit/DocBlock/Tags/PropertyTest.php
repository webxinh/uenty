<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\String_;


class PropertyTest extends \PHPUnit_Framework_TestCase
{
    
    public function testIfCorrectTagNameIsReturned()
    {
        $fixture = new Property('myProperty', null, new Description('Description'));

        $this->assertSame('property', $fixture->getName());
    }

    
    public function testIfTagCanBeRenderedUsingDefaultFormatter()
    {
        $fixture = new Property('myProperty', new String_(), new Description('Description'));
        $this->assertSame('@property string $myProperty Description', $fixture->render());

        $fixture = new Property('myProperty', null, new Description('Description'));
        $this->assertSame('@property $myProperty Description', $fixture->render());

        $fixture = new Property('myProperty');
        $this->assertSame('@property $myProperty', $fixture->render());
    }

    
    public function testIfTagCanBeRenderedUsingSpecificFormatter()
    {
        $fixture = new Property('myProperty');

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    
    public function testHasVariableName()
    {
        $expected = 'myProperty';

        $fixture = new Property($expected);

        $this->assertSame($expected, $fixture->getVariableName());
    }

    
    public function testHasType()
    {
        $expected = new String_();

        $fixture = new Property('myProperty', $expected);

        $this->assertSame($expected, $fixture->getType());
    }

    
    public function testHasDescription()
    {
        $expected = new Description('Description');

        $fixture = new Property('1.0', null, $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    
    public function testStringRepresentationIsReturned()
    {
        $fixture = new Property('myProperty', new String_(), new Description('Description'));

        $this->assertSame('string $myProperty Description', (string)$fixture);
    }

    
    public function testFactoryMethod()
    {
        $typeResolver = new TypeResolver();
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $context = new Context('');

        $description = new Description('My Description');
        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Property::create('string $myProperty My Description', $typeResolver, $descriptionFactory, $context);

        $this->assertSame('string $myProperty My Description', (string)$fixture);
        $this->assertSame('myProperty', $fixture->getVariableName());
        $this->assertInstanceOf(String_::class, $fixture->getType());
        $this->assertSame($description, $fixture->getDescription());
    }

    
    public function testFactoryMethodFailsIfEmptyBodyIsGiven()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        Property::create('', new TypeResolver(), $descriptionFactory);
    }

    
    public function testFactoryMethodFailsIfBodyIsNotString()
    {
        Property::create([]);
    }

    
    public function testFactoryMethodFailsIfResolverIsNull()
    {
        Property::create('body');
    }

    
    public function testFactoryMethodFailsIfDescriptionFactoryIsNull()
    {
        Property::create('body', new TypeResolver());
    }

    
    public function testExceptionIsThrownIfVariableNameIsNotString()
    {
        new Property([]);
    }
}
