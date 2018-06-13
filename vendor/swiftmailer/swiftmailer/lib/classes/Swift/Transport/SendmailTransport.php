<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Transport_SendmailTransport extends Swift_Transport_AbstractSmtpTransport
{
    
    private $_params = array(
        'timeout' => 30,
        'blocking' => 1,
        'command' => '/usr/sbin/sendmail -bs',
        'type' => Swift_Transport_IoBuffer::TYPE_PROCESS,
        );

    
    public function __construct(Swift_Transport_IoBuffer $buf, Swift_Events_EventDispatcher $dispatcher)
    {
        parent::__construct($buf, $dispatcher);
    }

    
    public function start()
    {
        if (false !== strpos($this->getCommand(), ' -bs')) {
            parent::start();
        }
    }

    
    public function setCommand($command)
    {
        $this->_params['command'] = $command;

        return $this;
    }

    
    public function getCommand()
    {
        return $this->_params['command'];
    }

    
    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $failedRecipients = (array) $failedRecipients;
        $command = $this->getCommand();
        $buffer = $this->getBuffer();
        $count = 0;

        if (false !== strpos($command, ' -t')) {
            if ($evt = $this->_eventDispatcher->createSendEvent($this, $message)) {
                $this->_eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
                if ($evt->bubbleCancelled()) {
                    return 0;
                }
            }

            if (false === strpos($command, ' -f')) {
                $command .= ' -f'.escapeshellarg($this->_getReversePath($message));
            }

            $buffer->initialize(array_merge($this->_params, array('command' => $command)));

            if (false === strpos($command, ' -i') && false === strpos($command, ' -oi')) {
                $buffer->setWriteTranslations(array("\r\n" => "\n", "\n." => "\n.."));
            } else {
                $buffer->setWriteTranslations(array("\r\n" => "\n"));
            }

            $count = count((array) $message->getTo())
                + count((array) $message->getCc())
                + count((array) $message->getBcc())
                ;
            $message->toByteStream($buffer);
            $buffer->flushBuffers();
            $buffer->setWriteTranslations(array());
            $buffer->terminate();

            if ($evt) {
                $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
                $evt->setFailedRecipients($failedRecipients);
                $this->_eventDispatcher->dispatchEvent($evt, 'sendPerformed');
            }

            $message->generateId();
        } elseif (false !== strpos($command, ' -bs')) {
            $count = parent::send($message, $failedRecipients);
        } else {
            $this->_throwException(new Swift_TransportException(
                'Unsupported sendmail command flags ['.$command.']. '.
                'Must be one of "-bs" or "-t" but can include additional flags.'
                ));
        }

        return $count;
    }

    
    protected function _getBufferParams()
    {
        return $this->_params;
    }
}
