<?php


namespace phpDocumentor\Reflection\DocBlock;

use Mockery as m;
use phpDocumentor\Reflection\DocBlock;


class SerializerTest extends \PHPUnit_Framework_TestCase
{
    
    public function testReconstructsADocCommentFromADocBlock()
    {
        $expected = <<<'DOCCOMMENT'

DOCCOMMENT;

        $fixture = new Serializer();

        $docBlock = new DocBlock(
            'This is a summary',
            new Description('This is a description'),
            [
                new DocBlock\Tags\Generic('unknown-tag', new Description('Test description for the unknown tag'))
            ]
        );

        $this->assertSame($expected, $fixture->getDocComment($docBlock));
    }

    
    public function testAddPrefixToDocBlock()
    {
        $expected = <<<'DOCCOMMENT'
aa
DOCCOMMENT;

        $fixture = new Serializer(2, 'a');

        $docBlock = new DocBlock(
            'This is a summary',
            new Description('This is a description'),
            [
                new DocBlock\Tags\Generic('unknown-tag', new Description('Test description for the unknown tag'))
            ]
        );

        $this->assertSame($expected, $fixture->getDocComment($docBlock));
    }

    
    public function testAddPrefixToDocBlockExceptFirstLine()
    {
        $expected = <<<'DOCCOMMENT'

DOCCOMMENT;

        $fixture = new Serializer(2, 'a', false);

        $docBlock = new DocBlock(
            'This is a summary',
            new Description('This is a description'),
            [
                new DocBlock\Tags\Generic('unknown-tag', new Description('Test description for the unknown tag'))
            ]
        );

        $this->assertSame($expected, $fixture->getDocComment($docBlock));
    }

    
    public function testWordwrapsAroundTheGivenAmountOfCharacters()
    {
        $expected = <<<'DOCCOMMENT'

DOCCOMMENT;

        $fixture = new Serializer(0, '', true, 15);

        $docBlock = new DocBlock(
            'This is a summary',
            new Description('This is a description'),
            [
                new DocBlock\Tags\Generic('unknown-tag', new Description('Test description for the unknown tag'))
            ]
        );

        $this->assertSame($expected, $fixture->getDocComment($docBlock));
    }

    
    public function testInitializationFailsIfIndentIsNotAnInteger()
    {
        new Serializer([]);
    }

    
    public function testInitializationFailsIfIndentStringIsNotAString()
    {
        new Serializer(0, []);
    }

    
    public function testInitializationFailsIfIndentFirstLineIsNotABoolean()
    {
        new Serializer(0, '', []);
    }

    
    public function testInitializationFailsIfLineLengthIsNotNullNorAnInteger()
    {
        new Serializer(0, '', false, []);
    }
}
