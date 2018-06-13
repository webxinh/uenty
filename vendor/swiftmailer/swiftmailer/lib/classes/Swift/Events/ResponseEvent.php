<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Events_ResponseEvent extends Swift_Events_EventObject
{
    
    private $_valid;

    
    private $_response;

    
    public function __construct(Swift_Transport $source, $response, $valid = false)
    {
        parent::__construct($source);
        $this->_response = $response;
        $this->_valid = $valid;
    }

    
    public function getResponse()
    {
        return $this->_response;
    }

    
    public function isValid()
    {
        return $this->_valid;
    }
}
