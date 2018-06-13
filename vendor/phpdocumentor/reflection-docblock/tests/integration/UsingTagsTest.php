<?php


namespace phpDocumentor\Reflection;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\See;


class UsingTagsTest extends \PHPUnit_Framework_TestCase
{
    public function testAddingYourOwnTagUsingAStaticMethodAsFactory()
    {
        
        include(__DIR__ . '/../../examples/04-adding-your-own-tag.php');

        $this->assertInstanceOf(\MyTag::class, $customTagObjects[0]);
        $this->assertSame('my-tag', $customTagObjects[0]->getName());
        $this->assertSame('I have a description', (string)$customTagObjects[0]->getDescription());
        $this->assertSame($docComment, $reconstitutedDocComment);
    }
}
