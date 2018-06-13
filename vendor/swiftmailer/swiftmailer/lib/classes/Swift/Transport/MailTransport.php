<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Transport_MailTransport implements Swift_Transport
{
    
    private $_extraParams = '-f%s';

    
    private $_eventDispatcher;

    
    private $_invoker;

    
    public function __construct(Swift_Transport_MailInvoker $invoker, Swift_Events_EventDispatcher $eventDispatcher)
    {
        @trigger_error(sprintf('The %s class is deprecated since version 5.4.5 and will be removed in 6.0. Use the Sendmail or SMTP transport instead.', __CLASS__), E_USER_DEPRECATED);

        $this->_invoker = $invoker;
        $this->_eventDispatcher = $eventDispatcher;
    }

    
    public function isStarted()
    {
        return false;
    }

    
    public function start()
    {
    }

    
    public function stop()
    {
    }

    
    public function setExtraParams($params)
    {
        $this->_extraParams = $params;

        return $this;
    }

    
    public function getExtraParams()
    {
        return $this->_extraParams;
    }

    
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $failedRecipients = (array) $failedRecipients;

        if ($evt = $this->_eventDispatcher->createSendEvent($this, $message)) {
            $this->_eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
            if ($evt->bubbleCancelled()) {
                return 0;
            }
        }

        $count = (
            count((array) $message->getTo())
            + count((array) $message->getCc())
            + count((array) $message->getBcc())
            );

        $toHeader = $message->getHeaders()->get('To');
        $subjectHeader = $message->getHeaders()->get('Subject');

        if (0 === $count) {
            $this->_throwException(new Swift_TransportException('Cannot send message without a recipient'));
        }
        $to = $toHeader ? $toHeader->getFieldBody() : '';
        $subject = $subjectHeader ? $subjectHeader->getFieldBody() : '';

        $reversePath = $this->_getReversePath($message);

        // Remove headers that would otherwise be duplicated
        $message->getHeaders()->remove('To');
        $message->getHeaders()->remove('Subject');

        $messageStr = $message->toString();

        if ($toHeader) {
            $message->getHeaders()->set($toHeader);
        }
        $message->getHeaders()->set($subjectHeader);

        // Separate headers from body
        if (false !== $endHeaders = strpos($messageStr, "\r\n\r\n")) {
            $headers = substr($messageStr, 0, $endHeaders)."\r\n"; //Keep last EOL
            $body = substr($messageStr, $endHeaders + 4);
        } else {
            $headers = $messageStr."\r\n";
            $body = '';
        }

        unset($messageStr);

        if ("\r\n" != PHP_EOL) {
            // Non-windows (not using SMTP)
            $headers = str_replace("\r\n", PHP_EOL, $headers);
            $subject = str_replace("\r\n", PHP_EOL, $subject);
            $body = str_replace("\r\n", PHP_EOL, $body);
        } else {
            // Windows, using SMTP
            $headers = str_replace("\r\n.", "\r\n..", $headers);
            $subject = str_replace("\r\n.", "\r\n..", $subject);
            $body = str_replace("\r\n.", "\r\n..", $body);
        }

        if ($this->_invoker->mail($to, $subject, $body, $headers, $this->_formatExtraParams($this->_extraParams, $reversePath))) {
            if ($evt) {
                $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
                $evt->setFailedRecipients($failedRecipients);
                $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
            }
        } else {
            $failedRecipients = array_merge(
                $failedRecipients,
                array_keys((array) $message->getTo()),
                array_keys((array) $message->getCc()),
                array_keys((array) $message->getBcc())
                );

            if ($evt) {
                $evt->setResult(Swift_Events_SendEvent::RESULT_FAILED);
                $evt->setFailedRecipients($failedRecipients);
                $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
            }

            $message->generateId();

            $count = 0;
        }

        return $count;
    }

    
    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        $this->_eventDispatcher->bindEventListener($plugin);
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

    
    private function _getReversePath(Swift_Mime_Message $message)
    {
        $return = $message->getReturnPath();
        $sender = $message->getSender();
        $from = $message->getFrom();
        $path = null;
        if (!empty($return)) {
            $path = $return;
        } elseif (!empty($sender)) {
            $keys = array_keys($sender);
            $path = array_shift($keys);
        } elseif (!empty($from)) {
            $keys = array_keys($from);
            $path = array_shift($keys);
        }

        return $path;
    }

    
    private function _isShellSafe($string)
    {
        // Future-proof
        if (escapeshellcmd($string) !== $string || !in_array(escapeshellarg($string), array("'$string'", "\"$string\""))) {
            return false;
        }

        $length = strlen($string);
        for ($i = 0; $i < $length; ++$i) {
            $c = $string[$i];
            // All other characters have a special meaning in at least one common shell, including = and +.
            // Full stop (.) has a special meaning in cmd.exe, but its impact should be negligible here.
            // Note that this does permit non-Latin alphanumeric characters based on the current locale.
            if (!ctype_alnum($c) && strpos('@_-.', $c) === false) {
                return false;
            }
        }

        return true;
    }

    
    private function _formatExtraParams($extraParams, $reversePath)
    {
        if (false !== strpos($extraParams, '-f%s')) {
            if (empty($reversePath) || false === $this->_isShellSafe($reversePath)) {
                $extraParams = str_replace('-f%s', '', $extraParams);
            } else {
                $extraParams = sprintf($extraParams, $reversePath);
            }
        }

        return !empty($extraParams) ? $extraParams : null;
    }
}
