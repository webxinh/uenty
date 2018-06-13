<?php
namespace phpDocumentor\Reflection;

interface DocBlockFactoryInterface
{
    
    public static function createInstance(array $additionalTags = []);

    
    public function create($docblock, Types\Context $context = null, Location $location = null);
}
