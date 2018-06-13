<?php


namespace phpDocumentor\Reflection;

use Mockery as m;
use phpDocumentor\Reflection\Types\Context;


class DocBlockTest extends \PHPUnit_Framework_TestCase
{
    
    public function testDocBlockCanHaveASummary()
    {
        $summary = 'This is a summary';

        $fixture = new DocBlock($summary);

        $this->assertSame($summary, $fixture->getSummary());
    }

    
    public function testExceptionIsThrownIfSummaryIsNotAString()
    {
        new DocBlock([]);
    }

    
    public function testExceptionIsThrownIfTemplateStartIsNotABoolean()
    {
        new DocBlock('', null, [], null, null, ['is not boolean']);
    }

    
    public function testExceptionIsThrownIfTemplateEndIsNotABoolean()
    {
        new DocBlock('', null, [], null, null, false, ['is not boolean']);
    }

    
    public function testDocBlockCanHaveADescription()
    {
        $description = new DocBlock\Description('');

        $fixture = new DocBlock('', $description);

        $this->assertSame($description, $fixture->getDescription());
    }

    
    public function testDocBlockCanHaveTags()
    {
        $tags = [
            m::mock(DocBlock\Tag::class)
        ];

        $fixture = new DocBlock('', null, $tags);

        $this->assertSame($tags, $fixture->getTags());
    }

    
    public function testDocBlockAllowsOnlyTags()
    {
        $tags = [
            null
        ];

        $fixture = new DocBlock('', null, $tags);
    }

    
    public function testFindTagsInDocBlockByName()
    {
        $tag1 = m::mock(DocBlock\Tag::class);
        $tag2 = m::mock(DocBlock\Tag::class);
        $tag3 = m::mock(DocBlock\Tag::class);
        $tags = [$tag1, $tag2, $tag3];

        $tag1->shouldReceive('getName')->andReturn('abc');
        $tag2->shouldReceive('getName')->andReturn('abcd');
        $tag3->shouldReceive('getName')->andReturn('ab');

        $fixture = new DocBlock('', null, $tags);

        $this->assertSame([$tag2], $fixture->getTagsByName('abcd'));
        $this->assertSame([], $fixture->getTagsByName('Ebcd'));
    }

    
    public function testExceptionIsThrownIfNameForTagsIsNotString()
    {
        $fixture = new DocBlock();
        $fixture->getTagsByName([]);
    }

    
    public function testCheckIfThereAreTagsWithAGivenName()
    {
        $tag1 = m::mock(DocBlock\Tag::class);
        $tag2 = m::mock(DocBlock\Tag::class);
        $tag3 = m::mock(DocBlock\Tag::class);
        $tags = [$tag1, $tag2, $tag3];

        $tag1->shouldReceive('getName')->twice()->andReturn('abc');
        $tag2->shouldReceive('getName')->twice()->andReturn('abcd');
        $tag3->shouldReceive('getName')->once();

        $fixture = new DocBlock('', null, $tags);

        $this->assertTrue($fixture->hasTag('abcd'));
        $this->assertFalse($fixture->hasTag('Ebcd'));
    }

    
    public function testExceptionIsThrownIfNameForCheckingTagsIsNotString()
    {
        $fixture = new DocBlock();
        $fixture->hasTag([]);
    }

    
    public function testDocBlockKnowsInWhichNamespaceItIsAndWhichAliasesThereAre()
    {
        $context = new Context('');

        $fixture = new DocBlock('', null, [], $context);

        $this->assertSame($context, $fixture->getContext());
    }

    
    public function testDocBlockKnowsAtWhichLineItIs()
    {
        $location = new Location(10);

        $fixture = new DocBlock('', null, [], null, $location);

        $this->assertSame($location, $fixture->getLocation());
    }

    
    public function testDocBlockKnowsIfItIsTheStartOfADocBlockTemplate()
    {
        $fixture = new DocBlock('', null, [], null, null, true);

        $this->assertTrue($fixture->isTemplateStart());
    }

    
    public function testDocBlockKnowsIfItIsTheEndOfADocBlockTemplate()
    {
        $fixture = new DocBlock('', null, [], null, null, false, true);

        $this->assertTrue($fixture->isTemplateEnd());
    }
}
