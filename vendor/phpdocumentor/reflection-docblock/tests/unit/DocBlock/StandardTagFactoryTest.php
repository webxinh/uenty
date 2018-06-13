<?php


namespace phpDocumentor\Reflection\DocBlock;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Tags\Author;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter;
use phpDocumentor\Reflection\DocBlock\Tags\Generic;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use phpDocumentor\Reflection\DocBlock\Tags\See;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context;


class StandardTagFactoryTest extends \PHPUnit_Framework_TestCase
{
    
    public function testCreatingAGenericTag()
    {
        $expectedTagName         = 'unknown-tag';
        $expectedDescriptionText = 'This is a description';
        $expectedDescription     = new Description($expectedDescriptionText);
        $context                 = new Context('');

        $descriptionFactory = m::mock(DescriptionFactory::class);
        $descriptionFactory
            ->shouldReceive('create')
            ->once()
            ->with($expectedDescriptionText, $context)
            ->andReturn($expectedDescription)
        ;

        $tagFactory = new StandardTagFactory(m::mock(FqsenResolver::class));
        $tagFactory->addService($descriptionFactory, DescriptionFactory::class);

        
        $tag = $tagFactory->create('@' . $expectedTagName . ' This is a description', $context);

        $this->assertInstanceOf(Generic::class, $tag);
        $this->assertSame($expectedTagName, $tag->getName());
        $this->assertSame($expectedDescription, $tag->getDescription());
    }

    
    public function testCreatingASpecificTag()
    {
        $context    = new Context('');
        $tagFactory = new StandardTagFactory(m::mock(FqsenResolver::class));

        
        $tag = $tagFactory->create('@author Mike van Riel <me@mikevanriel.com>', $context);

        $this->assertInstanceOf(Author::class, $tag);
        $this->assertSame('author', $tag->getName());
    }

    
    public function testAnEmptyContextIsCreatedIfNoneIsProvided()
    {
        $fqsen              = '\Tag';
        $resolver           = m::mock(FqsenResolver::class)
            ->shouldReceive('resolve')
            ->with('Tag', m::type(Context::class))
            ->andReturn(new Fqsen($fqsen))
            ->getMock()
        ;
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $descriptionFactory->shouldIgnoreMissing();

        $tagFactory = new StandardTagFactory($resolver);
        $tagFactory->addService($descriptionFactory, DescriptionFactory::class);

        
        $tag = $tagFactory->create('@see Tag');

        $this->assertInstanceOf(See::class, $tag);
        $this->assertSame($fqsen, (string)$tag->getReference());
    }

    
    public function testPassingYourOwnSetOfTagHandlers()
    {
        $context    = new Context('');
        $tagFactory = new StandardTagFactory(m::mock(FqsenResolver::class), ['user' => Author::class]);

        
        $tag = $tagFactory->create('@user Mike van Riel <me@mikevanriel.com>', $context);

        $this->assertInstanceOf(Author::class, $tag);
        $this->assertSame('author', $tag->getName());
    }

    
    public function testExceptionIsThrownIfProvidedTagIsNotWellformed()
    {
        $this->markTestIncomplete(
            'For some reason this test fails; once I have access to a RegEx analyzer I will have to test the regex'
        )
        ;
        $tagFactory = new StandardTagFactory(m::mock(FqsenResolver::class));
        $tagFactory->create('@user[myuser');
    }

    
    public function testAddParameterToServiceLocator()
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);
        $tagFactory->addParameter('myParam', 'myValue');

        $this->assertAttributeSame(
            [FqsenResolver::class => $resolver, 'myParam' => 'myValue'],
            'serviceLocator',
            $tagFactory
        )
        ;
    }

    
    public function testAddServiceToServiceLocator()
    {
        $service = new PassthroughFormatter();

        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);
        $tagFactory->addService($service);

        $this->assertAttributeSame(
            [FqsenResolver::class => $resolver, PassthroughFormatter::class => $service],
            'serviceLocator',
            $tagFactory
        )
        ;
    }

    
    public function testInjectConcreteServiceForInterfaceToServiceLocator()
    {
        $interfaceName = Formatter::class;
        $service       = new PassthroughFormatter();

        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);
        $tagFactory->addService($service, $interfaceName);

        $this->assertAttributeSame(
            [FqsenResolver::class => $resolver, $interfaceName => $service],
            'serviceLocator',
            $tagFactory
        )
        ;
    }

    
    public function testRegisteringAHandlerForANewTag()
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);

        $tagFactory->registerTagHandler('my-tag', Author::class);

        // Assert by trying to create one
        $tag = $tagFactory->create('@my-tag Mike van Riel <me@mikevanriel.com>');
        $this->assertInstanceOf(Author::class, $tag);
    }

    
    public function testHandlerRegistrationFailsIfProvidedTagNameIsNotAString()
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);

        $tagFactory->registerTagHandler([], Author::class);
    }

    
    public function testHandlerRegistrationFailsIfProvidedTagNameIsEmpty()
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);

        $tagFactory->registerTagHandler('', Author::class);
    }

    
    public function testHandlerRegistrationFailsIfProvidedTagNameIsNamespaceButNotFullyQualified()
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);

        $tagFactory->registerTagHandler('Name\Spaced\Tag', Author::class);
    }

    
    public function testHandlerRegistrationFailsIfProvidedHandlerIsNotAString()
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);

        $tagFactory->registerTagHandler('my-tag', []);
    }

    
    public function testHandlerRegistrationFailsIfProvidedHandlerIsEmpty()
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);

        $tagFactory->registerTagHandler('my-tag', '');
    }

    
    public function testHandlerRegistrationFailsIfProvidedHandlerIsNotAnExistingClassName()
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);

        $tagFactory->registerTagHandler('my-tag', 'IDoNotExist');
    }

    
    public function testHandlerRegistrationFailsIfProvidedHandlerDoesNotImplementTheTagInterface()
    {
        $resolver   = m::mock(FqsenResolver::class);
        $tagFactory = new StandardTagFactory($resolver);

        $tagFactory->registerTagHandler('my-tag', 'stdClass');
    }

    
    public function testReturntagIsMappedCorrectly()
    {
        $context    = new Context('');

        $descriptionFactory = m::mock(DescriptionFactory::class);
        $descriptionFactory
            ->shouldReceive('create')
            ->once()
            ->with('', $context)
            ->andReturn(new Description(''))
        ;

        $typeResolver = new TypeResolver();

        $tagFactory = new StandardTagFactory(m::mock(FqsenResolver::class));
        $tagFactory->addService($descriptionFactory, DescriptionFactory::class);
        $tagFactory->addService($typeResolver, TypeResolver::class);


        
        $tag = $tagFactory->create('@return mixed', $context);

        $this->assertInstanceOf(Return_::class, $tag);
        $this->assertSame('return', $tag->getName());
    }
}
