<?php

require_once(__DIR__ . '/../vendor/autoload.php');

use phpDocumentor\Reflection\DocBlock\Serializer;
use phpDocumentor\Reflection\DocBlockFactory;

$docComment = <<<DOCCOMMENT

DOCCOMMENT;

$factory  = DocBlockFactory::createInstance();
$docblock = $factory->create($docComment);

// Create the serializer that will reconstitute the DocBlock back to its original form.
$serializer = new Serializer();

// Reconstitution is performed by the `getDocComment()` method.
$reconstitutedDocComment = $serializer->getDocComment($docblock);

