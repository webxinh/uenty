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

use SebastianBergmann\Diff\Differ;


class ComparisonFailure extends \RuntimeException
{
    
    protected $expected;

    
    protected $actual;

    
    protected $expectedAsString;

    
    protected $actualAsString;

    
    protected $identical;

    
    protected $message;

    
    public function __construct($expected, $actual, $expectedAsString, $actualAsString, $identical = false, $message = '')
    {
        $this->expected         = $expected;
        $this->actual           = $actual;
        $this->expectedAsString = $expectedAsString;
        $this->actualAsString   = $actualAsString;
        $this->message          = $message;
    }

    
    public function getActual()
    {
        return $this->actual;
    }

    
    public function getExpected()
    {
        return $this->expected;
    }

    
    public function getActualAsString()
    {
        return $this->actualAsString;
    }

    
    public function getExpectedAsString()
    {
        return $this->expectedAsString;
    }

    
    public function getDiff()
    {
        if (!$this->actualAsString && !$this->expectedAsString) {
            return '';
        }

        $differ = new Differ("\n--- Expected\n+++ Actual\n");

        return $differ->diff($this->expectedAsString, $this->actualAsString);
    }

    
    public function toString()
    {
        return $this->message . $this->getDiff();
    }
}
