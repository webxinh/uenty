<?php


namespace phpDocumentor\Reflection\DocBlock;

use phpDocumentor\Reflection\DocBlock\Tags\Formatter;

interface Tag
{
    public function getName();

    public static function create($body);

    public function render(Formatter $formatter = null);

    public function __toString();
}
