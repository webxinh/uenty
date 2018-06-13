<?php


namespace phpDocumentor\Reflection\DocBlock;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock\Tags\Link;
use phpDocumentor\Reflection\Types\Context;


class DescriptionFactoryTest extends \PHPUnit_Framework_TestCase
{
    
    public function testDescriptionCanParseASimpleString($contents)
    {
        $tagFactory = m::mock(TagFactory::class);
        $tagFactory->shouldReceive('create')->never();

        $factory     = new DescriptionFactory($tagFactory);
        $description = $factory->create($contents, new Context(''));

        $this->assertSame($contents, $description->render());
    }

    
    public function testEscapeSequences($contents, $expected)
    {
        $tagFactory = m::mock(TagFactory::class);
        $tagFactory->shouldReceive('create')->never();

        $factory     = new DescriptionFactory($tagFactory);
        $description = $factory->create($contents, new Context(''));

        $this->assertSame($expected, $description->render());
    }

    
    public function testDescriptionCanParseAStringWithInlineTag()
    {
        $contents   = 'This is text for a {@link http://phpdoc.org/ description} that uses an inline tag.';
        $context    = new Context('');
        $tagFactory = m::mock(TagFactory::class);
        $tagFactory->shouldReceive('create')
            ->once()
            ->with('@link http://phpdoc.org/ description', $context)
            ->andReturn(new Link('http://phpdoc.org/', new Description('description')))
        ;

        $factory     = new DescriptionFactory($tagFactory);
        $description = $factory->create($contents, $context);

        $this->assertSame($contents, $description->render());
    }

    
    public function testDescriptionCanParseAStringStartingWithInlineTag()
    {
        $contents   = '{@link http://phpdoc.org/ This} is text for a description that starts with an inline tag.';
        $context    = new Context('');
        $tagFactory = m::mock(TagFactory::class);
        $tagFactory->shouldReceive('create')
            ->once()
            ->with('@link http://phpdoc.org/ This', $context)
            ->andReturn(new Link('http://phpdoc.org/', new Description('This')))
        ;

        $factory     = new DescriptionFactory($tagFactory);
        $description = $factory->create($contents, $context);

        $this->assertSame($contents, $description->render());
    }

    
    public function testIfSuperfluousStartingSpacesAreRemoved()
    {
        $factory         = new DescriptionFactory(m::mock(TagFactory::class));
        $descriptionText = <<<DESCRIPTION
This is a multiline
  description that you commonly
  see with tags.

      It does have a multiline code sample
      that should align, no matter what

  All spaces superfluous spaces on the
  second and later lines should be
  removed but the code sample should
  still be indented.
DESCRIPTION;

        $expectedDescription = <<<DESCRIPTION
This is a multiline
description that you commonly
see with tags.

    It does have a multiline code sample
    that should align, no matter what

All spaces superfluous spaces on the
second and later lines should be
removed but the code sample should
still be indented.
DESCRIPTION;

        $description = $factory->create($descriptionText, new Context(''));

        $this->assertSame($expectedDescription, $description->render());
    }

    
    public function provideSimpleExampleDescriptions()
    {
        return [
            ['This is text for a description.'],
            ['This is text for a description containing { that is literal.'],
            ['This is text for a description containing } that is literal.'],
            ['This is text for a description with {just a text} that is not a tag.'],
        ];
    }

    public function provideEscapeSequences()
    {
        return [
            ['This is text for a description with a {@}.', 'This is text for a description with a @.'],
            ['This is text for a description with a {}.', 'This is text for a description with a }.'],
        ];
    }
}
