<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Plugins_ThrottlerPlugin extends Swift_Plugins_BandwidthMonitorPlugin implements Swift_Plugins_Sleeper, Swift_Plugins_Timer
{
    
    const BYTES_PER_MINUTE = 0x01;

    
    const MESSAGES_PER_SECOND = 0x11;

    
    const MESSAGES_PER_MINUTE = 0x10;

    
    private $_sleeper;

    
    private $_timer;

    
    private $_start;

    
    private $_rate;

    
    private $_mode;

    
    private $_messages = 0;

    
    public function __construct($rate, $mode = self::BYTES_PER_MINUTE, Swift_Plugins_Sleeper $sleeper = null, Swift_Plugins_Timer $timer = null)
    {
        $this->_rate = $rate;
        $this->_mode = $mode;
        $this->_sleeper = $sleeper;
        $this->_timer = $timer;
    }

    
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        $time = $this->getTimestamp();
        if (!isset($this->_start)) {
            $this->_start = $time;
        }
        $duration = $time - $this->_start;

        switch ($this->_mode) {
            case self::BYTES_PER_MINUTE:
                $sleep = $this->_throttleBytesPerMinute($duration);
                break;
            case self::MESSAGES_PER_SECOND:
                $sleep = $this->_throttleMessagesPerSecond($duration);
                break;
            case self::MESSAGES_PER_MINUTE:
                $sleep = $this->_throttleMessagesPerMinute($duration);
                break;
            default:
                $sleep = 0;
                break;
        }

        if ($sleep > 0) {
            $this->sleep($sleep);
        }
    }

    
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        parent::sendPerformed($evt);
        ++$this->_messages;
    }

    
    public function sleep($seconds)
    {
        if (isset($this->_sleeper)) {
            $this->_sleeper->sleep($seconds);
        } else {
            sleep($seconds);
        }
    }

    
    public function getTimestamp()
    {
        if (isset($this->_timer)) {
            return $this->_timer->getTimestamp();
        }

        return time();
    }

    
    private function _throttleBytesPerMinute($timePassed)
    {
        $expectedDuration = $this->getBytesOut() / ($this->_rate / 60);

        return (int) ceil($expectedDuration - $timePassed);
    }

    
    private function _throttleMessagesPerSecond($timePassed)
    {
        $expectedDuration = $this->_messages / ($this->_rate);

        return (int) ceil($expectedDuration - $timePassed);
    }

    
    private function _throttleMessagesPerMinute($timePassed)
    {
        $expectedDuration = $this->_messages / ($this->_rate / 60);

        return (int) ceil($expectedDuration - $timePassed);
    }
}
