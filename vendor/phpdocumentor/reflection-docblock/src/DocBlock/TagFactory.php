<?php


namespace phpDocumentor\Reflection\DocBlock;

use phpDocumentor\Reflection\Types\Context as TypeContext;

interface TagFactory
{
    
    public function addParameter($name, $value);

    
    public function addService($service);

    
    public function create($tagLine, TypeContext $context = null);

    
    public function registerTagHandler($tagName, $handler);
}
