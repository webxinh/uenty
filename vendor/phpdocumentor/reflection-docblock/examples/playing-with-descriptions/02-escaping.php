<?php

require_once(__DIR__ . '/../../vendor/autoload.php');

use phpDocumentor\Reflection\DocBlockFactory;

$docComment = <<<DOCCOMMENT

DOCCOMMENT;

$factory  = DocBlockFactory::createInstance();
$docblock = $factory->create($docComment);

// Escaping is automatic so this happens in the DescriptionFactory.
$description = $docblock->getDescription();

// This is the rendition that we will receive of the Description.
$receivedDocComment = <<<DOCCOMMENT

DOCCOMMENT;

// Render it using the default PassthroughFormatter
$foundDescription = $description->render();
