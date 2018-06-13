<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Mailer_ArrayRecipientIterator implements Swift_Mailer_RecipientIterator
{
    
    private $_recipients = array();

    
    public function __construct(array $recipients)
    {
        $this->_recipients = $recipients;
    }

    
    public function hasNext()
    {
        return !empty($this->_recipients);
    }

    
    public function nextRecipient()
    {
        return array_splice($this->_recipients, 0, 1);
    }
}
