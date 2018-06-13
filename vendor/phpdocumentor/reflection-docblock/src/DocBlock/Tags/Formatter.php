<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Tag;

interface Formatter
{
    
    public function format(Tag $tag);
}
