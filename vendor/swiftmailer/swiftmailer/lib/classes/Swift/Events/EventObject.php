<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Events_EventObject implements Swift_Events_Event
{
    
    private $_source;

    
    private $_bubbleCancelled = false;

    
    public function __construct($source)
    {
        $this->_source = $source;
    }

    
    public function getSource()
    {
        return $this->_source;
    }

    
    public function cancelBubble($cancel = true)
    {
        $this->_bubbleCancelled = $cancel;
    }

    
    public function bubbleCancelled()
    {
        return $this->_bubbleCancelled;
    }
}
