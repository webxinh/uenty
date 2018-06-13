<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Plugins_ImpersonatePlugin implements Swift_Events_SendListener
{
    
    private $_sender;

    
    public function __construct($sender)
    {
        $this->_sender = $sender;
    }

    
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();
        $headers = $message->getHeaders();

        // save current recipients
        $headers->addPathHeader('X-Swift-Return-Path', $message->getReturnPath());

        // replace them with the one to send to
        $message->setReturnPath($this->_sender);
    }

    
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();

        // restore original headers
        $headers = $message->getHeaders();

        if ($headers->has('X-Swift-Return-Path')) {
            $message->setReturnPath($headers->get('X-Swift-Return-Path')->getAddress());
            $headers->removeAll('X-Swift-Return-Path');
        }
    }
}
