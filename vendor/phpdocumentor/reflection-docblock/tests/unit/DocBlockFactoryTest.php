<?php


namespace phpDocumentor\Reflection;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\TagFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\Types\Context;


class DocBlockFactoryTest extends \PHPUnit_Framework_TestCase
{
    
    public function testCreateFactoryUsingFactoryMethod()
    {
        $fixture = DocBlockFactory::createInstance();

        $this->assertInstanceOf(DocBlockFactory::class, $fixture);
    }

    
    public function testCreateDocBlockFromReflection()
    {
        $fixture = new DocBlockFactory(m::mock(DescriptionFactory::class), m::mock(TagFactory::class));

        $docBlock       = '';
        $classReflector = m::mock(\ReflectionClass::class);
        $classReflector->shouldReceive('getDocComment')->andReturn($docBlock);
        $docblock = $fixture->create($classReflector);

        $this->assertInstanceOf(DocBlock::class, $docblock);
        $this->assertSame('This is a DocBlock', $docblock->getSummary());
        $this->assertEquals(new Description(''), $docblock->getDescription());
        $this->assertSame([], $docblock->getTags());
        $this->assertEquals(new Context(''), $docblock->getContext());
        $this->assertNull($docblock->getLocation());
    }

    
    public function testCreateDocBlockFromStringWithDocComment()
    {
        $fixture = new DocBlockFactory(m::mock(DescriptionFactory::class), m::mock(TagFactory::class));

        $docblock = $fixture->create('');

        $this->assertInstanceOf(DocBlock::class, $docblock);
        $this->assertSame('This is a DocBlock', $docblock->getSummary());
        $this->assertEquals(new Description(''), $docblock->getDescription());
        $this->assertSame([], $docblock->getTags());
        $this->assertEquals(new Context(''), $docblock->getContext());
        $this->assertNull($docblock->getLocation());
    }

    
    public function testCreateDocBlockFromStringWithoutDocComment()
    {
        $fixture = new DocBlockFactory(m::mock(DescriptionFactory::class), m::mock(TagFactory::class));

        $docblock = $fixture->create('This is a DocBlock');

        $this->assertInstanceOf(DocBlock::class, $docblock);
        $this->assertSame('This is a DocBlock', $docblock->getSummary());
        $this->assertEquals(new Description(''), $docblock->getDescription());
        $this->assertSame([], $docblock->getTags());
        $this->assertEquals(new Context(''), $docblock->getContext());
        $this->assertNull($docblock->getLocation());
    }

    
    public function testSummaryAndDescriptionAreSeparated($given, $summary, $description)
    {
        $tagFactory = m::mock(TagFactory::class);
        $fixture    = new DocBlockFactory(new DescriptionFactory($tagFactory), $tagFactory);

        $docblock = $fixture->create($given);

        $this->assertSame($summary, $docblock->getSummary());
        $this->assertEquals(new Description($description), $docblock->getDescription());
    }

    
    public function testDescriptionsRetainFormatting()
    {
        $tagFactory = m::mock(TagFactory::class);
        $fixture    = new DocBlockFactory(new DescriptionFactory($tagFactory), $tagFactory);

        $given = <<<DOCBLOCK

DOCBLOCK;

        $description = <<<DESCRIPTION
This is a multiline Description
that contains a code block.

    See here: a CodeBlock
DESCRIPTION;

        $docblock = $fixture->create($given);

        $this->assertEquals(new Description($description), $docblock->getDescription());
    }

    
    public function testTagsAreInterpretedUsingFactory()
    {
        $tagString = <<<TAG
@author Mike van Riel <me@mikevanriel.com> This is with
  multiline description.
TAG;

        $tag        = m::mock(Tag::class);
        $tagFactory = m::mock(TagFactory::class);
        $tagFactory->shouldReceive('create')->with($tagString, m::type(Context::class))->andReturn($tag);

        $fixture = new DocBlockFactory(new DescriptionFactory($tagFactory), $tagFactory);

        $given = <<<DOCBLOCK

DOCBLOCK;

        $docblock = $fixture->create($given, new Context(''));

        $this->assertEquals([$tag], $docblock->getTags());
    }

    public function provideSummaryAndDescriptions()
    {
        return [
            ['This is a DocBlock', 'This is a DocBlock', ''],
            [
                'This is a DocBlock. This should still be summary.',
                'This is a DocBlock. This should still be summary.',
                ''
            ],
            [
                <<<DOCBLOCK
This is a DocBlock.
This should be a Description.
DOCBLOCK
                ,
                'This is a DocBlock.',
                'This should be a Description.'
            ],
            [
                <<<DOCBLOCK
This is a
multiline Summary.
This should be a Description.
DOCBLOCK
                ,
                "This is a\nmultiline Summary.",
                'This should be a Description.'
            ],
            [
                <<<DOCBLOCK
This is a Summary without dot but with a whiteline

This should be a Description.
DOCBLOCK
                ,
                'This is a Summary without dot but with a whiteline',
                'This should be a Description.'
            ],
            [
                <<<DOCBLOCK
This is a Summary with dot and with a whiteline.

This should be a Description.
DOCBLOCK
                ,
                'This is a Summary with dot and with a whiteline.',
                'This should be a Description.'
            ],
        ];
    }

    
    public function testTagsWithContextNamespace()
    {
        $tagFactoryMock = m::mock(TagFactory::class);
        $fixture = new DocBlockFactory(m::mock(DescriptionFactory::class), $tagFactoryMock);
        $context = new Context('MyNamespace');

        $tagFactoryMock->shouldReceive('create')->with(m::any(), $context)->andReturn(new Param('param'));
        $docblock = $fixture->create('', $context);
    }

    
    public function testTagsAreFilteredForNullValues()
    {
        $tagString = <<<TAG
@author Mike van Riel <me@mikevanriel.com> This is with
  multiline description.
TAG;

        $tagFactory = m::mock(TagFactory::class);
        $tagFactory->shouldReceive('create')->with($tagString, m::any())->andReturn(null);

        $fixture = new DocBlockFactory(new DescriptionFactory($tagFactory), $tagFactory);

        $given = <<<DOCBLOCK

DOCBLOCK;

        $docblock = $fixture->create($given, new Context(''));

        $this->assertEquals([], $docblock->getTags());
    }
}
