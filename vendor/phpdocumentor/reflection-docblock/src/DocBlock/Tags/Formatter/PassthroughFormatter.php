<?php


namespace phpDocumentor\Reflection\DocBlock\Tags\Formatter;

use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter;

class PassthroughFormatter implements Formatter
{
    
    public function format(Tag $tag)
    {
        return '@' . $tag->getName() . ' ' . (string)$tag;
    }
}
