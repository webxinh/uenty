<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Types\Context;


class CoversTest extends \PHPUnit_Framework_TestCase
{
    
    public function testIfCorrectTagNameIsReturned()
    {
        $fixture = new Covers(new Fqsen('\DateTime'), new Description('Description'));

        $this->assertSame('covers', $fixture->getName());
    }

    
    public function testIfTagCanBeRenderedUsingDefaultFormatter()
    {
        $fixture = new Covers(new Fqsen('\DateTime'), new Description('Description'));

        $this->assertSame('@covers \DateTime Description', $fixture->render());
    }

    
    public function testIfTagCanBeRenderedUsingSpecificFormatter()
    {
        $fixture = new Covers(new Fqsen('\DateTime'), new Description('Description'));

        $formatter = m::mock(Formatter::class);
        $formatter->shouldReceive('format')->with($fixture)->andReturn('Rendered output');

        $this->assertSame('Rendered output', $fixture->render($formatter));
    }

    
    public function testHasReferenceToFqsen()
    {
        $expected = new Fqsen('\DateTime');

        $fixture = new Covers($expected);

        $this->assertSame($expected, $fixture->getReference());
    }

    
    public function testHasDescription()
    {
        $expected = new Description('Description');

        $fixture = new Covers(new Fqsen('\DateTime'), $expected);

        $this->assertSame($expected, $fixture->getDescription());
    }

    
    public function testStringRepresentationIsReturned()
    {
        $fixture = new Covers(new Fqsen('\DateTime'), new Description('Description'));

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

        $fixture = Covers::create('DateTime My Description', $descriptionFactory, $resolver, $context);

        $this->assertSame('\DateTime My Description', (string)$fixture);
        $this->assertSame($fqsen, $fixture->getReference());
        $this->assertSame($description, $fixture->getDescription());
    }

    
    public function testFactoryMethodFailsIfBodyIsNotString()
    {
        $this->assertNull(Covers::create([]));
    }

    
    public function testFactoryMethodFailsIfBodyIsNotEmpty()
    {
        $this->assertNull(Covers::create(''));
    }
}
