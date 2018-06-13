<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


abstract class Swift_Transport_AbstractSmtpTransport implements Swift_Transport
{
    
    protected $_buffer;

    
    protected $_started = false;

    
    protected $_domain = '[127.0.0.1]';

    
    protected $_eventDispatcher;

    
    protected $_sourceIp;

    
    abstract protected function _getBufferParams();

    
    public function __construct(Swift_Transport_IoBuffer $buf, Swift_Events_EventDispatcher $dispatcher)
    {
        $this->_eventDispatcher = $dispatcher;
        $this->_buffer = $buf;
        $this->_lookupHostname();
    }

    
    public function setLocalDomain($domain)
    {
        $this->_domain = $domain;

        return $this;
    }

    
    public function getLocalDomain()
    {
        return $this->_domain;
    }

    
    public function setSourceIp($source)
    {
        $this->_sourceIp = $source;
    }

    
    public function getSourceIp()
    {
        return $this->_sourceIp;
    }

    
    public function start()
    {
        if (!$this->_started) {
            if ($evt = $this->_eventDispatcher->createTransportChangeEvent($this)) {
                $this->_eventDispatcher->dispatchEvent($evt, 'beforeTransportStarted');
                if ($evt->bubbleCancelled()) {
                    return;
                }
            }

            try {
                $this->_buffer->initialize($this->_getBufferParams());
            } catch (Swift_TransportException $e) {
                $this->_throwException($e);
            }
            $this->_readGreeting();
            $this->_doHeloCommand();

            if ($evt) {
                $this->_eventDispatcher->dispatchEvent($evt, 'transportStarted');
            }

            $this->_started = true;
        }
    }

    
    public function isStarted()
    {
        return $this->_started;
    }

    
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $sent = 0;
        $failedRecipients = (array) $failedRecipients;

        if ($evt = $this->_eventDispatcher->createSendEvent($this, $message)) {
            $this->_eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                return 0;
            }
        }

        if (!$reversePath = $this->_getReversePath($message)) {
            $this->_throwException(new Swift_TransportException(
                'Cannot send message without a sender address'
                )
            );
        }

        $to = (array) $message->getTo();
        $cc = (array) $message->getCc();
        $tos = array_merge($to, $cc);
        $bcc = (array) $message->getBcc();

        $message->setBcc(array());

        try {
            $sent += $this->_sendTo($message, $reversePath, $tos, $failedRecipients);
            $sent += $this->_sendBcc($message, $reversePath, $bcc, $failedRecipients);
        } catch (Exception $e) {
            $message->setBcc($bcc);
            throw $e;
        }

        $message->setBcc($bcc);

        if ($evt) {
            if ($sent == count($to) + count($cc) + count($bcc)) {
                $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
            } elseif ($sent > 0) {
                $evt->setResult(Swift_Events_SendEvent::RESULT_TENTATIVE);
            } else {
                $evt->setResult(Swift_Events_SendEvent::RESULT_FAILED);
            }
            $evt->setFailedRecipients($failedRecipients);
            $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
        }

        $message->generateId(); //Make sure a new Message ID is used

        return $sent;
    }

    
    public function stop()
    {
        if ($this->_started) {
            if ($evt = $this->_eventDispatcher->createTransportChangeEvent($this)) {
                $this->_eventDispatcher->dispatchEvent($evt, 'beforeTransportStopped');
                if ($evt->bubbleCancelled()) {
                    return;
                }
            }

            try {
                $this->executeCommand("QUIT\r\n", array(221));
            } catch (Swift_TransportException $e) {
            }

            try {
                $this->_buffer->terminate();

                if ($evt) {
                    $this->_eventDispatcher->dispatchEvent($evt, 'transportStopped');
                }
            } catch (Swift_TransportException $e) {
                $this->_throwException($e);
            }
        }
        $this->_started = false;
    }

    
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        $this->_eventDispatcher->bindEventListener($plugin);
    }

    
    public function reset()
    {
        $this->executeCommand("RSET\r\n", array(250));
    }

    
    public function getBuffer()
    {
        return $this->_buffer;
    }

    
    public function executeCommand($command, $codes = array(), &$failures = null)
    {
        $failures = (array) $failures;
        $seq = $this->_buffer->write($command);
        $response = $this->_getFullResponse($seq);
        if ($evt = $this->_eventDispatcher->createCommandEvent($this, $command, $codes)) {
            $this->_eventDispatcher->dispatchEvent($evt, 'commandSent');
        }
        $this->_assertResponseCode($response, $codes);

        return $response;
    }

    
    protected function _readGreeting()
    {
        $this->_assertResponseCode($this->_getFullResponse(0), array(220));
    }

    
    protected function _doHeloCommand()
    {
        $this->executeCommand(
            sprintf("HELO %s\r\n", $this->_domain), array(250)
            );
    }

    
    protected function _doMailFromCommand($address)
    {
        $this->executeCommand(
            sprintf("MAIL FROM:<%s>\r\n", $address), array(250)
            );
    }

    
    protected function _doRcptToCommand($address)
    {
        $this->executeCommand(
            sprintf("RCPT TO:<%s>\r\n", $address), array(250, 251, 252)
            );
    }

    
    protected function _doDataCommand()
    {
        $this->executeCommand("DATA\r\n", array(354));
    }

    
    protected function _streamMessage(Swift_Mime_Message $message)
    {
        $this->_buffer->setWriteTranslations(array("\r\n." => "\r\n.."));
        try {
            $message->toByteStream($this->_buffer);
            $this->_buffer->flushBuffers();
        } catch (Swift_TransportException $e) {
            $this->_throwException($e);
        }
        $this->_buffer->setWriteTranslations(array());
        $this->executeCommand("\r\n.\r\n", array(250));
    }

    
    protected function _getReversePath(Swift_Mime_Message $message)
    {
        $return = $message->getReturnPath();
        $sender = $message->getSender();
        $from = $message->getFrom();
        $path = null;
        if (!empty($return)) {
            $path = $return;
        } elseif (!empty($sender)) {
            // Don't use array_keys
            reset($sender); // Reset Pointer to first pos
            $path = key($sender); // Get key
        } elseif (!empty($from)) {
            reset($from); // Reset Pointer to first pos
            $path = key($from); // Get key
        }

        return $path;
    }

    
    protected function _throwException(Swift_TransportException $e)
    {
        if ($evt = $this->_eventDispatcher->createTransportExceptionEvent($this, $e)) {
            $this->_eventDispatcher->dispatchEvent($evt, 'exceptionThrown');
            if (!$evt->bubbleCancelled()) {
                throw $e;
            }
        } else {
            throw $e;
        }
    }

    
    protected function _assertResponseCode($response, $wanted)
    {
        list($code) = sscanf($response, '%3d');
        $valid = (empty($wanted) || in_array($code, $wanted));

        if ($evt = $this->_eventDispatcher->createResponseEvent($this, $response,
            $valid)) {
            $this->_eventDispatcher->dispatchEvent($evt, 'responseReceived');
        }

        if (!$valid) {
            $this->_throwException(
                new Swift_TransportException(
                    'Expected response code '.implode('/', $wanted).' but got code '.
                    '"'.$code.'", with message "'.$response.'"',
                    $code)
                );
        }
    }

    
    protected function _getFullResponse($seq)
    {
        $response = '';
        try {
            do {
                $line = $this->_buffer->readLine($seq);
                $response .= $line;
            } while (null !== $line && false !== $line && ' ' != $line{3});
        } catch (Swift_TransportException $e) {
            $this->_throwException($e);
        } catch (Swift_IoException $e) {
            $this->_throwException(
                new Swift_TransportException(
                    $e->getMessage())
                );
        }

        return $response;
    }

    
    private function _doMailTransaction($message, $reversePath, array $recipients, array &$failedRecipients)
    {
        $sent = 0;
        $this->_doMailFromCommand($reversePath);
        foreach ($recipients as $forwardPath) {
            try {
                $this->_doRcptToCommand($forwardPath);
                ++$sent;
            } catch (Swift_TransportException $e) {
                $failedRecipients[] = $forwardPath;
            }
        }

        if ($sent != 0) {
            $this->_doDataCommand();
            $this->_streamMessage($message);
        } else {
            $this->reset();
        }

        return $sent;
    }

    
    private function _sendTo(Swift_Mime_Message $message, $reversePath, array $to, array &$failedRecipients)
    {
        if (empty($to)) {
            return 0;
        }

        return $this->_doMailTransaction($message, $reversePath, array_keys($to),
            $failedRecipients);
    }

    
    private function _sendBcc(Swift_Mime_Message $message, $reversePath, array $bcc, array &$failedRecipients)
    {
        $sent = 0;
        foreach ($bcc as $forwardPath => $name) {
            $message->setBcc(array($forwardPath => $name));
            $sent += $this->_doMailTransaction(
                $message, $reversePath, array($forwardPath), $failedRecipients
                );
        }

        return $sent;
    }

    
    private function _lookupHostname()
    {
        if (!empty($_SERVER['SERVER_NAME']) && $this->_isFqdn($_SERVER['SERVER_NAME'])) {
            $this->_domain = $_SERVER['SERVER_NAME'];
        } elseif (!empty($_SERVER['SERVER_ADDR'])) {
            // Set the address literal tag (See RFC 5321, section: 4.1.3)
            if (false === strpos($_SERVER['SERVER_ADDR'], ':')) {
                $prefix = ''; // IPv4 addresses are not tagged.
            } else {
                $prefix = 'IPv6:'; // Adding prefix in case of IPv6.
            }

            $this->_domain = sprintf('[%s%s]', $prefix, $_SERVER['SERVER_ADDR']);
        }
    }

    
    private function _isFqdn($hostname)
    {
        // We could do a really thorough check, but there's really no point
        if (false !== $dotPos = strpos($hostname, '.')) {
            return ($dotPos > 0) && ($dotPos != strlen($hostname) - 1);
        }

        return false;
    }

    
    public function __destruct()
    {
        $this->stop();
    }
}
