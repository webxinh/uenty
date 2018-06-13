<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Plugins_Loggers_EchoLogger implements Swift_Plugins_Logger
{
    
    private $_isHtml;

    
    public function __construct($isHtml = true)
    {
        $this->_isHtml = $isHtml;
    }

    
    public function add($entry)
    {
        if ($this->_isHtml) {
            printf('%s%s%s', htmlspecialchars($entry, ENT_QUOTES), '<br />', PHP_EOL);
        } else {
            printf('%s%s', $entry, PHP_EOL);
        }
    }

    
    public function clear()
    {
    }

    
    public function dump()
    {
    }
}
