<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier <fabien.potencier@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Transport_SpoolTransport implements Swift_Transport
{
    
    private $_spool;

    
    private $_eventDispatcher;

    
    public function __construct(Swift_Events_EventDispatcher $eventDispatcher, Swift_Spool $spool = null)
    {
        $this->_eventDispatcher = $eventDispatcher;
        $this->_spool = $spool;
    }

    
    public function setSpool(Swift_Spool $spool)
    {
        $this->_spool = $spool;

        return $this;
    }

    
    public function getSpool()
    {
        return $this->_spool;
    }

    
    public function isStarted()
    {
        return true;
    }

    
    public function start()
    {
    }

    
    public function stop()
    {
    }

    
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        if ($evt = $this->_eventDispatcher->createSendEvent($this, $message)) {
            $this->_eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                return 0;
            }
        }

        $success = $this->_spool->queueMessage($message);

        if ($evt) {
            $evt->setResult($success ? Swift_Events_SendEvent::RESULT_SPOOLED : Swift_Events_SendEvent::RESULT_FAILED);
            $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
        }

        return 1;
    }

    
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        $this->_eventDispatcher->bindEventListener($plugin);
    }
}
