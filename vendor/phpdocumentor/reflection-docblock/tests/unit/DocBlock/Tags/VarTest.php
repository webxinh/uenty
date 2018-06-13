<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\String_;


class VarTest extends \PHPUnit_Framework_TestCase
{
    
    public function testIfCorrectTagNameIsReturned()
    {
        $fixture = new Var_('myVariable', null, new Description('Description'));

        $this->assertSame('var', $fixture->getName());
    }

    
    public function testIfTagCanBeRenderedUsingDefaultFormatter()
    {
        $fixture = new Var_('myVariable', new String_(), new Description('Description'));
        $this->assertSame('@var string $myVariable Description', $fixture->render());

        $fixture = new Var_('myVariable', null, new Description('Description'));
        $this->assertSame('@var $myVariable Description', $fixture->render());

        $fixture = new Var_('myVariable');
        $this->assertSame('@var $myVariable', $fixture->render());
    }

    
    public function testIfTagCanBeRenderedUsingSpecificFormatter()
    {
        $fixture = new Var_('myVariable');

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    
    public function testHasVariableName()
    {
        $expected = 'myVariable';

        $fixture = new Var_($expected);

        $this->assertSame($expected, $fixture->getVariableName());
    }

    
    public function testHasType()
    {
        $expected = new String_();

        $fixture = new Var_('myVariable', $expected);

        $this->assertSame($expected, $fixture->getType());
    }

    
    public function testHasDescription()
    {
        $expected = new Description('Description');

        $fixture = new Var_('1.0', null, $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    
    public function testStringRepresentationIsReturned()
    {
        $fixture = new Var_('myVariable', new String_(), new Description('Description'));

        $this->assertSame('string $myVariable Description', (string)$fixture);
    }

    
    public function testFactoryMethod()
    {
        $typeResolver       = new TypeResolver();
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $context            = new Context('');

        $description = new Description('My Description');
        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Var_::create('string $myVariable My Description', $typeResolver, $descriptionFactory, $context);

        $this->assertSame('string $myVariable My Description', (string)$fixture);
        $this->assertSame('myVariable', $fixture->getVariableName());
        $this->assertInstanceOf(String_::class, $fixture->getType());
        $this->assertSame($description, $fixture->getDescription());
    }

    
    public function testFactoryMethodFailsIfEmptyBodyIsGiven()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        Var_::create('', new TypeResolver(), $descriptionFactory);
    }

    
    public function testFactoryMethodFailsIfBodyIsNotString()
    {
        Var_::create([]);
    }

    
    public function testFactoryMethodFailsIfResolverIsNull()
    {
        Var_::create('body');
    }

    
    public function testFactoryMethodFailsIfDescriptionFactoryIsNull()
    {
        Var_::create('body', new TypeResolver());
    }

    
    public function testExceptionIsThrownIfVariableNameIsNotString()
    {
        new Var_([]);
    }
}
