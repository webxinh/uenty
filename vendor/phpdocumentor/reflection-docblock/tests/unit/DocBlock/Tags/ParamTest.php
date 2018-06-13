<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\String_;


class ParamTest extends \PHPUnit_Framework_TestCase
{
    
    public function testIfCorrectTagNameIsReturned()
    {
        $fixture = new Param('myParameter', null, false, new Description('Description'));

        $this->assertSame('param', $fixture->getName());
    }

    
    public function testIfTagCanBeRenderedUsingDefaultFormatter()
    {
        $fixture = new Param('myParameter', new String_(), true, new Description('Description'));
        $this->assertSame('@param string ...$myParameter Description', $fixture->render());

        $fixture = new Param('myParameter', new String_(), false, new Description('Description'));
        $this->assertSame('@param string $myParameter Description', $fixture->render());

        $fixture = new Param('myParameter', null, false, new Description('Description'));
        $this->assertSame('@param $myParameter Description', $fixture->render());

        $fixture = new Param('myParameter');
        $this->assertSame('@param $myParameter', $fixture->render());
    }

    
    public function testIfTagCanBeRenderedUsingSpecificFormatter()
    {
        $fixture = new Param('myParameter');

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    
    public function testHasVariableName()
    {
        $expected = 'myParameter';

        $fixture = new Param($expected);

        $this->assertSame($expected, $fixture->getVariableName());
    }

    
    public function testHasType()
    {
        $expected = new String_();

        $fixture = new Param('myParameter', $expected);

        $this->assertSame($expected, $fixture->getType());
    }

    
    public function testIfParameterIsVariadic()
    {
        $fixture = new Param('myParameter', new String_(), false);
        $this->assertFalse($fixture->isVariadic());

        $fixture = new Param('myParameter', new String_(), true);
        $this->assertTrue($fixture->isVariadic());
    }

    
    public function testHasDescription()
    {
        $expected = new Description('Description');

        $fixture = new Param('1.0', null, false, $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    
    public function testStringRepresentationIsReturned()
    {
        $fixture = new Param('myParameter', new String_(), true, new Description('Description'));

        $this->assertSame('string ...$myParameter Description', (string)$fixture);
    }

    
    public function testFactoryMethod()
    {
        $typeResolver = new TypeResolver();
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $context = new Context('');

        $description = new Description('My Description');
        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Param::create('string ...$myParameter My Description', $typeResolver, $descriptionFactory, $context);

        $this->assertSame('string ...$myParameter My Description', (string)$fixture);
        $this->assertSame('myParameter', $fixture->getVariableName());
        $this->assertInstanceOf(String_::class, $fixture->getType());
        $this->assertTrue($fixture->isVariadic());
        $this->assertSame($description, $fixture->getDescription());
    }

    
    public function testFactoryMethodFailsIfEmptyBodyIsGiven()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        Param::create('', new TypeResolver(), $descriptionFactory);
    }

    
    public function testFactoryMethodFailsIfBodyIsNotString()
    {
        Param::create([]);
    }

    
    public function testFactoryMethodFailsIfResolverIsNull()
    {
        Param::create('body');
    }

    
    public function testFactoryMethodFailsIfDescriptionFactoryIsNull()
    {
        Param::create('body', new TypeResolver());
    }

    
    public function testExceptionIsThrownIfVariableNameIsNotString()
    {
        new Param([]);
    }

    
    public function testExceptionIsThrownIfVariadicIsNotBoolean()
    {
        new Param('', null, []);
    }
}
