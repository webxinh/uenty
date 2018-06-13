<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Plugins_AntiFloodPlugin implements Swift_Events_SendListener, Swift_Plugins_Sleeper
{
    
    private $_threshold;

    
    private $_sleep;

    
    private $_counter = 0;

    
    private $_sleeper;

    
    public function __construct($threshold = 99, $sleep = 0, Swift_Plugins_Sleeper $sleeper = null)
    {
        $this->setThreshold($threshold);
        $this->setSleepTime($sleep);
        $this->_sleeper = $sleeper;
    }

    
    public function setThreshold($threshold)
    {
        $this->_threshold = $threshold;
    }

    
    public function getThreshold()
    {
        return $this->_threshold;
    }

    
    public function setSleepTime($sleep)
    {
        $this->_sleep = $sleep;
    }

    
    public function getSleepTime()
    {
        return $this->_sleep;
    }

    
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
    }

    
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        ++$this->_counter;
        if ($this->_counter >= $this->_threshold) {
            $transport = $evt->getTransport();
            $transport->stop();
            if ($this->_sleep) {
                $this->sleep($this->_sleep);
            }
            $transport->start();
            $this->_counter = 0;
        }
    }

    
    public function sleep($seconds)
    {
        if (isset($this->_sleeper)) {
            $this->_sleeper->sleep($seconds);
        } else {
            sleep($seconds);
        }
    }
}
