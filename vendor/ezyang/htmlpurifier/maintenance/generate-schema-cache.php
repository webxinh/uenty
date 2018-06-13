#!/usr/bin/php
<?php

require_once dirname(__FILE__) . '/common.php';
require_once dirname(__FILE__) . '/../library/HTMLPurifier.auto.php';
assertCli();



$target = dirname(__FILE__) . '/../library/HTMLPurifier/ConfigSchema/schema.ser';

$builder = new HTMLPurifier_ConfigSchema_InterchangeBuilder();
$interchange = new HTMLPurifier_ConfigSchema_Interchange();

$builder->buildDir($interchange);

$loader = dirname(__FILE__) . '/../config-schema.php';
if (file_exists($loader)) include $loader;
foreach ($_SERVER['argv'] as $i => $dir) {
    if ($i === 0) continue;
    $builder->buildDir($interchange, realpath($dir));
}

$interchange->validate();

$schema_builder = new HTMLPurifier_ConfigSchema_Builder_ConfigSchema();
$schema = $schema_builder->build($interchange);

echo "Saving schema... ";
file_put_contents($target, serialize($schema));
echo "done!\n";

// vim: et sw=4 sts=4
