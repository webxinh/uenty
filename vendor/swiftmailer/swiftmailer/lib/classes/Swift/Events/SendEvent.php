<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Events_SendEvent extends Swift_Events_EventObject
{
    
    const RESULT_PENDING = 0x0001;

    
    const RESULT_SPOOLED = 0x0011;

    
    const RESULT_SUCCESS = 0x0010;

    
    const RESULT_TENTATIVE = 0x0100;

    
    const RESULT_FAILED = 0x1000;

    
    private $_message;

    
    private $_failedRecipients = array();

    
    private $_result;

    
    public function __construct(Swift_Transport $source, Swift_Mime_Message $message)
    {
        parent::__construct($source);
        $this->_message = $message;
        $this->_result = self::RESULT_PENDING;
    }

    
    public function getTransport()
    {
        return $this->getSource();
    }

    
    public function getMessage()
    {
        return $this->_message;
    }

    
    public function setFailedRecipients($recipients)
    {
        $this->_failedRecipients = $recipients;
    }

    
    public function getFailedRecipients()
    {
        return $this->_failedRecipients;
    }

    
    public function setResult($result)
    {
        $this->_result = $result;
    }

    
    public function getResult()
    {
        return $this->_result;
    }
}
