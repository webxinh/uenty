<?php

namespace Codeception;

use Codeception\Test\Metadata;

interface TestInterface extends \PHPUnit_Framework_Test
{
    
    public function getMetadata();
}
