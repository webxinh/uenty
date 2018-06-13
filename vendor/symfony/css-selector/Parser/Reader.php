<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\CssSelector\Parser;


class Reader
{
    
    private $source;

    
    private $length;

    
    private $position = 0;

    
    public function __construct($source)
    {
        $this->source = $source;
        $this->length = strlen($source);
    }

    
    public function isEOF()
    {
        return $this->position >= $this->length;
    }

    
    public function getPosition()
    {
        return $this->position;
    }

    
    public function getRemainingLength()
    {
        return $this->length - $this->position;
    }

    
    public function getSubstring($length, $offset = 0)
    {
        return substr($this->source, $this->position + $offset, $length);
    }

    
    public function getOffset($string)
    {
        $position = strpos($this->source, $string, $this->position);

        return false === $position ? false : $position - $this->position;
    }

    
    public function findPattern($pattern)
    {
        $source = substr($this->source, $this->position);

        if (preg_match($pattern, $source, $matches)) {
            return $matches;
        }

        return false;
    }

    
    public function moveForward($length)
    {
        $this->position += $length;
    }

    public function moveToEnd()
    {
        $this->position = $this->length;
    }
}
