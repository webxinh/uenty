<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Plugins_Loggers_ArrayLogger implements Swift_Plugins_Logger
{
    
    private $_log = array();

    
    private $_size = 0;

    
    public function __construct($size = 50)
    {
        $this->_size = $size;
    }

    
    public function add($entry)
    {
        $this->_log[] = $entry;
        while (count($this->_log) > $this->_size) {
            array_shift($this->_log);
        }
    }

    
    public function clear()
    {
        $this->_log = array();
    }

    
    public function dump()
    {
        return implode(PHP_EOL, $this->_log);
    }
}
