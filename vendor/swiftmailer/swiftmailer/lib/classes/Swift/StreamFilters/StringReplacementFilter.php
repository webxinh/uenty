<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_StreamFilters_StringReplacementFilter implements Swift_StreamFilter
{
    
    private $_search;

    
    private $_replace;

    
    public function __construct($search, $replace)
    {
        $this->_search = $search;
        $this->_replace = $replace;
    }

    
    public function shouldBuffer($buffer)
    {
        $endOfBuffer = substr($buffer, -1);
        foreach ((array) $this->_search as $needle) {
            if (false !== strpos($needle, $endOfBuffer)) {
                return true;
            }
        }

        return false;
    }

    
    public function filter($buffer)
    {
        return str_replace($this->_search, $this->_replace, $buffer);
    }
}
