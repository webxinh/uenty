<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Plugins_BandwidthMonitorPlugin implements Swift_Events_SendListener, Swift_Events_CommandListener, Swift_Events_ResponseListener, Swift_InputByteStream
{
    
    private $_out = 0;

    
    private $_in = 0;

    
    private $_mirrors = array();

    
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
    }

    
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();
        $message->toByteStream($this);
    }

    
    public function commandSent(Swift_Events_CommandEvent $evt)
    {
        $command = $evt->getCommand();
        $this->_out += strlen($command);
    }

    
    public function responseReceived(Swift_Events_ResponseEvent $evt)
    {
        $response = $evt->getResponse();
        $this->_in += strlen($response);
    }

    
    public function write($bytes)
    {
        $this->_out += strlen($bytes);
        foreach ($this->_mirrors as $stream) {
            $stream->write($bytes);
        }
    }

    
    public function commit()
    {
    }

    
    public function bind(Swift_InputByteStream $is)
    {
        $this->_mirrors[] = $is;
    }

    
    public function unbind(Swift_InputByteStream $is)
    {
        foreach ($this->_mirrors as $k => $stream) {
            if ($is === $stream) {
                unset($this->_mirrors[$k]);
            }
        }
    }

    
    public function flushBuffers()
    {
        foreach ($this->_mirrors as $stream) {
            $stream->flushBuffers();
        }
    }

    
    public function getBytesOut()
    {
        return $this->_out;
    }

    
    public function getBytesIn()
    {
        return $this->_in;
    }

    
    public function reset()
    {
        $this->_out = 0;
        $this->_in = 0;
    }
}
