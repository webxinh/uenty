<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Integer;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\String_;
use phpDocumentor\Reflection\Types\Void_;


class MethodTest extends \PHPUnit_Framework_TestCase
{
    
    public function testIfCorrectTagNameIsReturned()
    {
        $fixture = new Method('myMethod');

        $this->assertSame('method', $fixture->getName());
    }

    
    public function testIfTagCanBeRenderedUsingDefaultFormatter()
    {
        $arguments = [
            ['name' => 'argument1', 'type' => new String_()],
            ['name' => 'argument2', 'type' => new Object_()]
        ];
        $fixture = new Method('myMethod', $arguments, new Void_(), true, new Description('My Description'));

        $this->assertSame(
            '@method static void myMethod(string $argument1, object $argument2) My Description',
            $fixture->render()
        );
    }

    
    public function testIfTagCanBeRenderedUsingSpecificFormatter()
    {
        $fixture = new Method('myMethod');

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    
    public function testHasMethodName()
    {
        $expected = 'myMethod';

        $fixture = new Method($expected);

        $this->assertSame($expected, $fixture->getMethodName());
    }

    
    public function testHasArguments()
    {
        $arguments = [
            [ 'name' => 'argument1', 'type' => new String_() ]
        ];

        $fixture = new Method('myMethod', $arguments);

        $this->assertSame($arguments, $fixture->getArguments());
    }

    
    public function testArgumentsMayBePassedAsString()
    {
        $arguments = ['argument1'];
        $expected = [
            [ 'name' => $arguments[0], 'type' => new Void_() ]
        ];

        $fixture = new Method('myMethod', $arguments);

        $this->assertEquals($expected, $fixture->getArguments());
    }

    
    public function testArgumentTypeCanBeInferredAsVoid()
    {
        $arguments = [ [ 'name' => 'argument1' ] ];
        $expected = [
            [ 'name' => $arguments[0]['name'], 'type' => new Void_() ]
        ];

        $fixture = new Method('myMethod', $arguments);

        $this->assertEquals($expected, $fixture->getArguments());
    }

    
    public function testRestArgumentIsParsedAsRegularArg()
    {
        $expected = [
            [ 'name' => 'arg1', 'type' => new Void_() ],
            [ 'name' => 'rest', 'type' => new Void_() ],
            [ 'name' => 'rest2', 'type' => new Array_() ],
        ];

        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = new TypeResolver();
        $context            = new Context('');
        $description  = new Description('');
        $descriptionFactory->shouldReceive('create')->with('', $context)->andReturn($description);

        $fixture = Method::create(
            'void myMethod($arg1, ...$rest, array ... $rest2)',
            $resolver,
            $descriptionFactory,
            $context
        );

        $this->assertEquals($expected, $fixture->getArguments());
    }

    
    public function testHasReturnType()
    {
        $expected = new String_();

        $fixture = new Method('myMethod', [], $expected);

        $this->assertSame($expected, $fixture->getReturnType());
    }

    
    public function testReturnTypeCanBeInferredAsVoid()
    {
        $fixture = new Method('myMethod', []);

        $this->assertEquals(new Void_(), $fixture->getReturnType());
    }

    
    public function testMethodCanBeStatic()
    {
        $expected = false;
        $fixture = new Method('myMethod', [], null, $expected);
        $this->assertSame($expected, $fixture->isStatic());

        $expected = true;
        $fixture = new Method('myMethod', [], null, $expected);
        $this->assertSame($expected, $fixture->isStatic());
    }

    
    public function testHasDescription()
    {
        $expected = new Description('Description');

        $fixture = new Method('myMethod', [], null, false, $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    
    public function testStringRepresentationIsReturned()
    {
        $arguments = [
            ['name' => 'argument1', 'type' => new String_()],
            ['name' => 'argument2', 'type' => new Object_()]
        ];
        $fixture = new Method('myMethod', $arguments, new Void_(), true, new Description('My Description'));

        $this->assertSame(
            'static void myMethod(string $argument1, object $argument2) My Description',
            (string)$fixture
        );
    }

    
    public function testFactoryMethod()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = new TypeResolver();
        $context            = new Context('');

        $description  = new Description('My Description');
        $expectedArguments = [
            [ 'name' => 'argument1', 'type' => new String_() ],
            [ 'name' => 'argument2', 'type' => new Void_() ]
        ];

        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Method::create(
            'static void myMethod(string $argument1, $argument2) My Description',
            $resolver,
            $descriptionFactory,
            $context
        );

        $this->assertSame('static void myMethod(string $argument1, void $argument2) My Description', (string)$fixture);
        $this->assertSame('myMethod', $fixture->getMethodName());
        $this->assertEquals($expectedArguments, $fixture->getArguments());
        $this->assertInstanceOf(Void_::class, $fixture->getReturnType());
        $this->assertSame($description, $fixture->getDescription());
    }

    public function collectionReturnTypesProvider()
    {
        return [
            ['int[]',    Array_::class, Integer::class, Compound::class],
            ['int[][]',  Array_::class, Array_::class,  Compound::class],
            ['Object[]', Array_::class, Object_::class, Compound::class],
            ['array[]',  Array_::class, Array_::class,  Compound::class],
        ];
    }

    
    public function testCollectionReturnTypes(
        $returnType,
        $expectedType,
        $expectedValueType = null,
        $expectedKeyType = null
    ) { $resolver           = new TypeResolver();
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $descriptionFactory->shouldReceive('create')->with('', null)->andReturn(new Description(''));

        $fixture = Method::create("$returnType myMethod(\$arg)", $resolver, $descriptionFactory);
        $returnType = $fixture->getReturnType();
        $this->assertInstanceOf($expectedType, $returnType);

        if ($returnType instanceof Array_) {
            $this->assertInstanceOf($expectedValueType, $returnType->getValueType());
            $this->assertInstanceOf($expectedKeyType, $returnType->getKeyType());
        }
    }

    
    public function testFactoryMethodFailsIfBodyIsNotString()
    {
        Method::create([]);
    }

    
    public function testFactoryMethodFailsIfBodyIsEmpty()
    {
        Method::create('');
    }

    
    public function testFactoryMethodReturnsNullIfBodyIsIncorrect()
    {
        $this->assertNull(Method::create('body('));
    }

    
    public function testFactoryMethodFailsIfResolverIsNull()
    {
        Method::create('body');
    }

    
    public function testFactoryMethodFailsIfDescriptionFactoryIsNull()
    {
        Method::create('body', new TypeResolver());
    }

    
    public function testCreationFailsIfBodyIsNotString()
    {
        new Method([]);
    }

    
    public function testCreationFailsIfBodyIsEmpty()
    {
        new Method('');
    }

    
    public function testCreationFailsIfStaticIsNotBoolean()
    {
        new Method('body', [], null, []);
    }

    
    public function testCreationFailsIfArgumentRecordContainsInvalidEntry()
    {
        new Method('body', [ [ 'name' => 'myName', 'unknown' => 'nah' ] ]);
    }

    
    public function testCreateMethodParenthesisMissing()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver           = new TypeResolver();
        $context            = new Context('');

        $description  = new Description('My Description');

        $descriptionFactory->shouldReceive('create')->with('My Description', $context)->andReturn($description);

        $fixture = Method::create(
            'static void myMethod My Description',
            $resolver,
            $descriptionFactory,
            $context
        );

        $this->assertSame('static void myMethod() My Description', (string)$fixture);
        $this->assertSame('myMethod', $fixture->getMethodName());
        $this->assertEquals([], $fixture->getArguments());
        $this->assertInstanceOf(Void_::class, $fixture->getReturnType());
        $this->assertSame($description, $fixture->getDescription());
    }
}
