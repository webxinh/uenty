<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Util_TestSuiteIterator implements RecursiveIterator
{
    
    protected $position;

    
    protected $tests;

    
    public function __construct(PHPUnit_Framework_TestSuite $testSuite)
    {
        $this->tests = $testSuite->tests();
    }

    
    public function rewind()
    {
        $this->position = 0;
    }

    
    public function valid()
    {
        return $this->position < count($this->tests);
    }

    
    public function key()
    {
        return $this->position;
    }

    
    public function current()
    {
        return $this->valid() ? $this->tests[$this->position] : null;
    }

    
    public function next()
    {
        $this->position++;
    }

    
    public function getChildren()
    {
        return new self(
            $this->tests[$this->position]
        );
    }

    
    public function hasChildren()
    {
        return $this->tests[$this->position] instanceof PHPUnit_Framework_TestSuite;
    }
}
