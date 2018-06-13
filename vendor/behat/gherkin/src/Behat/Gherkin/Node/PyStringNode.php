<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Node;


class PyStringNode implements ArgumentInterface
{
    
    private $strings = array();
    
    private $line;

    
    public function __construct(array $strings, $line)
    {
        $this->strings = $strings;
        $this->line = $line;
    }

    
    public function getNodeType()
    {
        return 'PyString';
    }

    
    public function getStrings()
    {
        return $this->strings;
    }

    
    public function getRaw()
    {
        return implode("\n", $this->strings);
    }

    
    public function __toString()
    {
        return $this->getRaw();
    }

    
    public function getLine()
    {
        return $this->line;
    }
}
