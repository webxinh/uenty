<?php


namespace phpDocumentor\Reflection;

use Mockery as m;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Compound;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\Object_;


class TypeResolverTest extends \PHPUnit_Framework_TestCase
{
    
    public function testResolvingKeywords($keyword, $expectedClass)
    {
        $fixture = new TypeResolver();

        $resolvedType = $fixture->resolve($keyword, new Context(''));

        $this->assertInstanceOf($expectedClass, $resolvedType);
    }

    
    public function testResolvingFQSENs($fqsen)
    {
        $fixture = new TypeResolver();

        
        $resolvedType = $fixture->resolve($fqsen, new Context(''));

        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Object_', $resolvedType);
        $this->assertInstanceOf('phpDocumentor\Reflection\Fqsen', $resolvedType->getFqsen());
        $this->assertSame($fqsen, (string)$resolvedType);
    }

    
    public function testResolvingRelativeQSENsBasedOnNamespace()
    {
        $fixture = new TypeResolver();

        
        $resolvedType = $fixture->resolve('DocBlock', new Context('phpDocumentor\Reflection'));

        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Object_', $resolvedType);
        $this->assertInstanceOf('phpDocumentor\Reflection\Fqsen', $resolvedType->getFqsen());
        $this->assertSame('\phpDocumentor\Reflection\DocBlock', (string)$resolvedType);
    }

    
    public function testResolvingRelativeQSENsBasedOnNamespaceAlias()
    {
        $fixture = new TypeResolver();

        
        $resolvedType = $fixture->resolve(
            'm\MockInterface',
            new Context('phpDocumentor\Reflection', ['m' => '\Mockery'])
        );

        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Object_', $resolvedType);
        $this->assertInstanceOf('phpDocumentor\Reflection\Fqsen', $resolvedType->getFqsen());
        $this->assertSame('\Mockery\MockInterface', (string)$resolvedType);
    }

    
    public function testResolvingTypedArrays()
    {
        $fixture = new TypeResolver();

        
        $resolvedType = $fixture->resolve('string[]', new Context(''));

        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Array_', $resolvedType);
        $this->assertSame('string[]', (string)$resolvedType);
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Compound', $resolvedType->getKeyType());
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\String_', $resolvedType->getValueType());
    }

    
    public function testResolvingNestedTypedArrays()
    {
        $fixture = new TypeResolver();

        
        $resolvedType = $fixture->resolve('string[][]', new Context(''));

        
        $childValueType = $resolvedType->getValueType();

        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Array_', $resolvedType);

        $this->assertSame('string[][]', (string)$resolvedType);
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Compound', $resolvedType->getKeyType());
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Array_', $childValueType);

        $this->assertSame('string[]', (string)$childValueType);
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Compound', $childValueType->getKeyType());
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\String_', $childValueType->getValueType());
    }

    
    public function testResolvingCompoundTypes()
    {
        $fixture = new TypeResolver();

        
        $resolvedType = $fixture->resolve('string|Reflection\DocBlock', new Context('phpDocumentor'));

        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Compound', $resolvedType);
        $this->assertSame('string|\phpDocumentor\Reflection\DocBlock', (string)$resolvedType);

        
        $firstType = $resolvedType->get(0);

        
        $secondType = $resolvedType->get(1);

        $this->assertInstanceOf('phpDocumentor\Reflection\Types\String_', $firstType);
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Object_', $secondType);
        $this->assertInstanceOf('phpDocumentor\Reflection\Fqsen', $secondType->getFqsen());
    }

    
    public function testResolvingCompoundTypedArrayTypes()
    {
        $fixture = new TypeResolver();

        
        $resolvedType = $fixture->resolve('\stdClass[]|Reflection\DocBlock[]', new Context('phpDocumentor'));

        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Compound', $resolvedType);
        $this->assertSame('\stdClass[]|\phpDocumentor\Reflection\DocBlock[]', (string)$resolvedType);

        
        $firstType = $resolvedType->get(0);

        
        $secondType = $resolvedType->get(1);

        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Array_', $firstType);
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Array_', $secondType);
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Object_', $firstType->getValueType());
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Object_', $secondType->getValueType());
    }

    
    public function testResolvingCompoundTypesWithTwoArrays()
    {
        $fixture = new TypeResolver();

        
        $resolvedType = $fixture->resolve('integer[]|string[]', new Context(''));

        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Compound', $resolvedType);
        $this->assertSame('int[]|string[]', (string)$resolvedType);

        
        $firstType = $resolvedType->get(0);

        
        $secondType = $resolvedType->get(1);

        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Array_', $firstType);
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Integer', $firstType->getValueType());
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\Array_', $secondType);
        $this->assertInstanceOf('phpDocumentor\Reflection\Types\String_', $secondType->getValueType());
    }

    
    public function testAddingAKeyword()
    {
        // Assign
        $typeMock = m::mock(Type::class);

        // Act
        $fixture = new TypeResolver();
        $fixture->addKeyword('mock', get_class($typeMock));

        // Assert
        $result = $fixture->resolve('mock', new Context(''));
        $this->assertInstanceOf(get_class($typeMock), $result);
        $this->assertNotSame($typeMock, $result);
    }

    
    public function testAddingAKeywordFailsIfTypeClassDoesNotExist()
    {
        $fixture = new TypeResolver();
        $fixture->addKeyword('mock', 'IDoNotExist');
    }

    
    public function testAddingAKeywordFailsIfTypeClassDoesNotImplementTypeInterface()
    {
        $fixture = new TypeResolver();
        $fixture->addKeyword('mock', 'stdClass');
    }

    
    public function testExceptionIsThrownIfTypeIsEmpty()
    {
        $fixture = new TypeResolver();
        $fixture->resolve(' ', new Context(''));
    }

    
    public function testExceptionIsThrownIfTypeIsNotAString()
    {
        $fixture = new TypeResolver();
        $fixture->resolve(['a'], new Context(''));
    }

    
    public function provideKeywords()
    {
        return [
            ['string', 'phpDocumentor\Reflection\Types\String_'],
            ['int', 'phpDocumentor\Reflection\Types\Integer'],
            ['integer', 'phpDocumentor\Reflection\Types\Integer'],
            ['float', 'phpDocumentor\Reflection\Types\Float_'],
            ['double', 'phpDocumentor\Reflection\Types\Float_'],
            ['bool', 'phpDocumentor\Reflection\Types\Boolean'],
            ['boolean', 'phpDocumentor\Reflection\Types\Boolean'],
            ['resource', 'phpDocumentor\Reflection\Types\Resource'],
            ['null', 'phpDocumentor\Reflection\Types\Null_'],
            ['callable', 'phpDocumentor\Reflection\Types\Callable_'],
            ['callback', 'phpDocumentor\Reflection\Types\Callable_'],
            ['array', 'phpDocumentor\Reflection\Types\Array_'],
            ['scalar', 'phpDocumentor\Reflection\Types\Scalar'],
            ['object', 'phpDocumentor\Reflection\Types\Object_'],
            ['mixed', 'phpDocumentor\Reflection\Types\Mixed'],
            ['void', 'phpDocumentor\Reflection\Types\Void_'],
            ['$this', 'phpDocumentor\Reflection\Types\This'],
            ['static', 'phpDocumentor\Reflection\Types\Static_'],
            ['self', 'phpDocumentor\Reflection\Types\Self_'],
        ];
    }

    
    public function provideFqcn()
    {
        return [
            'namespace' => ['\phpDocumentor\Reflection'],
            'class'     => ['\phpDocumentor\Reflection\DocBlock'],
        ];
    }
}
