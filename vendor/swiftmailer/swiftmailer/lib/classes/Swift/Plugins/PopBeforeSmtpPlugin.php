<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Plugins_PopBeforeSmtpPlugin implements Swift_Events_TransportChangeListener, Swift_Plugins_Pop_Pop3Connection
{
    
    private $_connection;

    
    private $_host;

    
    private $_port;

    
    private $_crypto;

    
    private $_username;

    
    private $_password;

    
    private $_socket;

    
    private $_timeout = 10;

    
    private $_transport;

    
    public function __construct($host, $port = 110, $crypto = null)
    {
        $this->_host = $host;
        $this->_port = $port;
        $this->_crypto = $crypto;
    }

    
    public static function newInstance($host, $port = 110, $crypto = null)
    {
        return new self($host, $port, $crypto);
    }

    
    public function setConnection(Swift_Plugins_Pop_Pop3Connection $connection)
    {
        $this->_connection = $connection;

        return $this;
    }

    
    public function bindSmtp(Swift_Transport $smtp)
    {
        $this->_transport = $smtp;
    }

    
    public function setTimeout($timeout)
    {
        $this->_timeout = (int) $timeout;

        return $this;
    }

    
    public function setUsername($username)
    {
        $this->_username = $username;

        return $this;
    }

    
    public function setPassword($password)
    {
        $this->_password = $password;

        return $this;
    }

    
    public function connect()
    {
        if (isset($this->_connection)) {
            $this->_connection->connect();
        } else {
            if (!isset($this->_socket)) {
                if (!$socket = fsockopen(
                    $this->_getHostString(), $this->_port, $errno, $errstr, $this->_timeout)) {
                    throw new Swift_Plugins_Pop_Pop3Exception(
                        sprintf('Failed to connect to POP3 host [%s]: %s', $this->_host, $errstr)
                    );
                }
                $this->_socket = $socket;

                if (false === $greeting = fgets($this->_socket)) {
                    throw new Swift_Plugins_Pop_Pop3Exception(
                        sprintf('Failed to connect to POP3 host [%s]', trim($greeting))
                    );
                }

                $this->_assertOk($greeting);

                if ($this->_username) {
                    $this->_command(sprintf("USER %s\r\n", $this->_username));
                    $this->_command(sprintf("PASS %s\r\n", $this->_password));
                }
            }
        }
    }

    
    public function disconnect()
    {
        if (isset($this->_connection)) {
            $this->_connection->disconnect();
        } else {
            $this->_command("QUIT\r\n");
            if (!fclose($this->_socket)) {
                throw new Swift_Plugins_Pop_Pop3Exception(
                    sprintf('POP3 host [%s] connection could not be stopped', $this->_host)
                );
            }
            $this->_socket = null;
        }
    }

    
    public function beforeTransportStarted(Swift_Events_TransportChangeEvent $evt)
    {
        if (isset($this->_transport)) {
            if ($this->_transport !== $evt->getTransport()) {
                return;
            }
        }

        $this->connect();
        $this->disconnect();
    }

    
    public function transportStarted(Swift_Events_TransportChangeEvent $evt)
    {
    }

    
    public function beforeTransportStopped(Swift_Events_TransportChangeEvent $evt)
    {
    }

    
    public function transportStopped(Swift_Events_TransportChangeEvent $evt)
    {
    }

    private function _command($command)
    {
        if (!fwrite($this->_socket, $command)) {
            throw new Swift_Plugins_Pop_Pop3Exception(
                sprintf('Failed to write command [%s] to POP3 host', trim($command))
            );
        }

        if (false === $response = fgets($this->_socket)) {
            throw new Swift_Plugins_Pop_Pop3Exception(
                sprintf('Failed to read from POP3 host after command [%s]', trim($command))
            );
        }

        $this->_assertOk($response);

        return $response;
    }

    private function _assertOk($response)
    {
        if (substr($response, 0, 3) != '+OK') {
            throw new Swift_Plugins_Pop_Pop3Exception(
                sprintf('POP3 command failed [%s]', trim($response))
            );
        }
    }

    private function _getHostString()
    {
        $host = $this->_host;
        switch (strtolower($this->_crypto)) {
            case 'ssl':
                $host = 'ssl://'.$host;
                break;

            case 'tls':
                $host = 'tls://'.$host;
                break;
        }

        return $host;
    }
}
