<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Transport_FailoverTransport extends Swift_Transport_LoadBalancedTransport
{
    
    private $_currentTransport;

    // needed as __construct is called from elsewhere explicitly
    public function __construct()
    {
        parent::__construct();
    }

    
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $maxTransports = count($this->_transports);
        $sent = 0;
        $this->_lastUsedTransport = null;

        for ($i = 0; $i < $maxTransports
            && $transport = $this->_getNextTransport(); ++$i) {
            try {
                if (!$transport->isStarted()) {
                    $transport->start();
                }

                if ($sent = $transport->send($message, $failedRecipients)) {
                    $this->_lastUsedTransport = $transport;

                    return $sent;
                }
            } catch (Swift_TransportException $e) {
                $this->_killCurrentTransport();
            }
        }

        if (count($this->_transports) == 0) {
            throw new Swift_TransportException(
                'All Transports in FailoverTransport failed, or no Transports available'
                );
        }

        return $sent;
    }

    protected function _getNextTransport()
    {
        if (!isset($this->_currentTransport)) {
            $this->_currentTransport = parent::_getNextTransport();
        }

        return $this->_currentTransport;
    }

    protected function _killCurrentTransport()
    {
        $this->_currentTransport = null;
        parent::_killCurrentTransport();
    }
}
