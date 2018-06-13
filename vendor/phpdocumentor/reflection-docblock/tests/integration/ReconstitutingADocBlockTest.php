<?php


namespace phpDocumentor\Reflection;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\See;


class ReconstitutingADocBlockTest extends \PHPUnit_Framework_TestCase
{
    public function testReconstituteADocBlock()
    {
        
        include(__DIR__ . '/../../examples/03-reconstituting-a-docblock.php');

        $this->assertSame($docComment, $reconstitutedDocComment);
    }
}
