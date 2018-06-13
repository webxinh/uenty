<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Types\Context;


class SeeTest extends \PHPUnit_Framework_TestCase
{
    
    public function testIfCorrectTagNameIsReturned()
    {
        $fixture = new See(new Fqsen('\DateTime'), new Description('Description'));

        $this->assertSame('see', $fixture->getName());
    }

    
    public function testIfTagCanBeRenderedUsingDefaultFormatter()
    {
        $fixture = new See(new Fqsen('\DateTime'), new Description('Description'));

        $this->assertSame('@see \DateTime Description', $fixture->render());
    }

    
    public function testIfTagCanBeRenderedUsingSpecificFormatter()
    {
        $fixture = new See(new Fqsen('\DateTime'), new Description('Description'));

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    
    public function testHasReferenceToFqsen()
    {
        $expected = new Fqsen('\DateTime');

        $fixture = new See($expected);

        $this->assertSame($expected, $fixture->getReference());
    }

    
    public function testHasDescription()
    {
        $expected = new Description('Description');

        $fixture = new See(new Fqsen('\DateTime'), $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    
    public function testStringRepresentationIsReturned()
    {
        $fixture = new See(new Fqsen('\DateTime'), new Description('Description'));

        $this->assertSame('\DateTime Description', (string)$fixture);
    }

    
    public function testFactoryMethod()
    {
        $descriptionFactory = m::mock(DescriptionFactory::class);
        $resolver = m::mock(FqsenResolver::class);
        $context = new Context('');

        $fqsen = new Fqsen('\DateTime');
        $description = new Description('My Description');

        $descriptionFactory
            ->shouldReceive('create')->with('My Description', $context)->andReturn($description);
        $resolver->shouldReceive('resolve')->with('DateTime', $context)->andReturn($fqsen);

        $fixture = See::create('DateTime My Description', $resolver, $descriptionFactory, $context);

        $this->assertSame('\DateTime My Description', (string)$fixture);
        $this->assertSame($fqsen, $fixture->getReference());
        $this->assertSame($description, $fixture->getDescription());
    }

    
    public function testFactoryMethodFailsIfBodyIsNotString()
    {
        $this->assertNull(See::create([]));
    }

    
    public function testFactoryMethodFailsIfBodyIsNotEmpty()
    {
        $this->assertNull(See::create(''));
    }

    
    public function testFactoryMethodFailsIfResolverIsNull()
    {
        See::create('body');
    }

    
    public function testFactoryMethodFailsIfDescriptionFactoryIsNull()
    {
        See::create('body', new FqsenResolver());
    }
}
