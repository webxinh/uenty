<?php
/*
 * This file is part of the Comparator package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\Comparator;

use SebastianBergmann\Exporter\Exporter;


abstract class Comparator
{
    
    protected $factory;

    
    protected $exporter;

    public function __construct()
    {
        $this->exporter = new Exporter;
    }

    
    public function setFactory(Factory $factory)
    {
        $this->factory = $factory;
    }

    
    abstract public function accepts($expected, $actual);

    
    abstract public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false);
}
