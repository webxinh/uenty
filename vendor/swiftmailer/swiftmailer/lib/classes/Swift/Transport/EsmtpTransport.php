<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Transport_EsmtpTransport extends Swift_Transport_AbstractSmtpTransport implements Swift_Transport_SmtpAgent
{
    
    private $_handlers = array();

    
    private $_capabilities = array();

    
    private $_params = array(
        'protocol' => 'tcp',
        'host' => 'localhost',
        'port' => 25,
        'timeout' => 30,
        'blocking' => 1,
        'tls' => false,
        'type' => Swift_Transport_IoBuffer::TYPE_SOCKET,
        'stream_context_options' => array(),
        );

    
    public function __construct(Swift_Transport_IoBuffer $buf, array $extensionHandlers, Swift_Events_EventDispatcher $dispatcher)
    {
        parent::__construct($buf, $dispatcher);
        $this->setExtensionHandlers($extensionHandlers);
    }

    
    public function setHost($host)
    {
        $this->_params['host'] = $host;

        return $this;
    }

    
    public function getHost()
    {
        return $this->_params['host'];
    }

    
    public function setPort($port)
    {
        $this->_params['port'] = (int) $port;

        return $this;
    }

    
    public function getPort()
    {
        return $this->_params['port'];
    }

    
    public function setTimeout($timeout)
    {
        $this->_params['timeout'] = (int) $timeout;
        $this->_buffer->setParam('timeout', (int) $timeout);

        return $this;
    }

    
    public function getTimeout()
    {
        return $this->_params['timeout'];
    }

    
    public function setEncryption($encryption)
    {
        $encryption = strtolower($encryption);
        if ('tls' == $encryption) {
            $this->_params['protocol'] = 'tcp';
            $this->_params['tls'] = true;
        } else {
            $this->_params['protocol'] = $encryption;
            $this->_params['tls'] = false;
        }

        return $this;
    }

    
    public function getEncryption()
    {
        return $this->_params['tls'] ? 'tls' : $this->_params['protocol'];
    }

    
    public function setStreamOptions($options)
    {
        $this->_params['stream_context_options'] = $options;

        return $this;
    }

    
    public function getStreamOptions()
    {
        return $this->_params['stream_context_options'];
    }

    
    public function setSourceIp($source)
    {
        $this->_params['sourceIp'] = $source;

        return $this;
    }

    
    public function getSourceIp()
    {
        return isset($this->_params['sourceIp']) ? $this->_params['sourceIp'] : null;
    }

    
    public function setExtensionHandlers(array $handlers)
    {
        $assoc = array();
        foreach ($handlers as $handler) {
            $assoc[$handler->getHandledKeyword()] = $handler;
        }

        @uasort($assoc, array($this, '_sortHandlers'));
        $this->_handlers = $assoc;
        $this->_setHandlerParams();

        return $this;
    }

    
    public function getExtensionHandlers()
    {
        return array_values($this->_handlers);
    }

    
    public function executeCommand($command, $codes = array(), &$failures = null)
    {
        $failures = (array) $failures;
        $stopSignal = false;
        $response = null;
        foreach ($this->_getActiveHandlers() as $handler) {
            $response = $handler->onCommand(
                $this, $command, $codes, $failures, $stopSignal
                );
            if ($stopSignal) {
                return $response;
            }
        }

        return parent::executeCommand($command, $codes, $failures);
    }

    // -- Mixin invocation code

    
    public function __call($method, $args)
    {
        foreach ($this->_handlers as $handler) {
            if (in_array(strtolower($method),
                array_map('strtolower', (array) $handler->exposeMixinMethods())
                )) {
                $return = call_user_func_array(array($handler, $method), $args);
                // Allow fluid method calls
                if (is_null($return) && substr($method, 0, 3) == 'set') {
                    return $this;
                } else {
                    return $return;
                }
            }
        }
        trigger_error('Call to undefined method '.$method, E_USER_ERROR);
    }

    
    protected function _getBufferParams()
    {
        return $this->_params;
    }

    
    protected function _doHeloCommand()
    {
        try {
            $response = $this->executeCommand(
                sprintf("EHLO %s\r\n", $this->_domain), array(250)
                );
        } catch (Swift_TransportException $e) {
            return parent::_doHeloCommand();
        }

        if ($this->_params['tls']) {
            try {
                $this->executeCommand("STARTTLS\r\n", array(220));

                if (!$this->_buffer->startTLS()) {
                    throw new Swift_TransportException('Unable to connect with TLS encryption');
                }

                try {
                    $response = $this->executeCommand(
                        sprintf("EHLO %s\r\n", $this->_domain), array(250)
                        );
                } catch (Swift_TransportException $e) {
                    return parent::_doHeloCommand();
                }
            } catch (Swift_TransportException $e) {
                $this->_throwException($e);
            }
        }

        $this->_capabilities = $this->_getCapabilities($response);
        $this->_setHandlerParams();
        foreach ($this->_getActiveHandlers() as $handler) {
            $handler->afterEhlo($this);
        }
    }

    
    protected function _doMailFromCommand($address)
    {
        $handlers = $this->_getActiveHandlers();
        $params = array();
        foreach ($handlers as $handler) {
            $params = array_merge($params, (array) $handler->getMailParams());
        }
        $paramStr = !empty($params) ? ' '.implode(' ', $params) : '';
        $this->executeCommand(
            sprintf("MAIL FROM:<%s>%s\r\n", $address, $paramStr), array(250)
            );
    }

    
    protected function _doRcptToCommand($address)
    {
        $handlers = $this->_getActiveHandlers();
        $params = array();
        foreach ($handlers as $handler) {
            $params = array_merge($params, (array) $handler->getRcptParams());
        }
        $paramStr = !empty($params) ? ' '.implode(' ', $params) : '';
        $this->executeCommand(
            sprintf("RCPT TO:<%s>%s\r\n", $address, $paramStr), array(250, 251, 252)
            );
    }

    
    private function _getCapabilities($ehloResponse)
    {
        $capabilities = array();
        $ehloResponse = trim($ehloResponse);
        $lines = explode("\r\n", $ehloResponse);
        array_shift($lines);
        foreach ($lines as $line) {
            if (preg_match('/^[0-9]{3}[ -]([A-Z0-9-]+)((?:[ =].*)?)$/Di', $line, $matches)) {
                $keyword = strtoupper($matches[1]);
                $paramStr = strtoupper(ltrim($matches[2], ' ='));
                $params = !empty($paramStr) ? explode(' ', $paramStr) : array();
                $capabilities[$keyword] = $params;
            }
        }

        return $capabilities;
    }

    
    private function _setHandlerParams()
    {
        foreach ($this->_handlers as $keyword => $handler) {
            if (array_key_exists($keyword, $this->_capabilities)) {
                $handler->setKeywordParams($this->_capabilities[$keyword]);
            }
        }
    }

    
    private function _getActiveHandlers()
    {
        $handlers = array();
        foreach ($this->_handlers as $keyword => $handler) {
            if (array_key_exists($keyword, $this->_capabilities)) {
                $handlers[] = $handler;
            }
        }

        return $handlers;
    }

    
    private function _sortHandlers($a, $b)
    {
        return $a->getPriorityOver($b->getHandledKeyword());
    }
}
