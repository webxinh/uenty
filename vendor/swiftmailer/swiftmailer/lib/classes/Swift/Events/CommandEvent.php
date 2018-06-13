<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Events_CommandEvent extends Swift_Events_EventObject
{
    
    private $_command;

    
    private $_successCodes = array();

    
    public function __construct(Swift_Transport $source, $command, $successCodes = array())
    {
        parent::__construct($source);
        $this->_command = $command;
        $this->_successCodes = $successCodes;
    }

    
    public function getCommand()
    {
        return $this->_command;
    }

    
    public function getSuccessCodes()
    {
        return $this->_successCodes;
    }
}
