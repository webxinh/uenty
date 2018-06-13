<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Transport_Esmtp_AuthHandler implements Swift_Transport_EsmtpHandler
{
    
    private $_authenticators = array();

    
    private $_username;

    
    private $_password;

    
    private $_auth_mode;

    
    private $_esmtpParams = array();

    
    public function __construct(array $authenticators)
    {
        $this->setAuthenticators($authenticators);
    }

    
    public function setAuthenticators(array $authenticators)
    {
        $this->_authenticators = $authenticators;
    }

    
    public function getAuthenticators()
    {
        return $this->_authenticators;
    }

    
    public function setUsername($username)
    {
        $this->_username = $username;
    }

    
    public function getUsername()
    {
        return $this->_username;
    }

    
    public function setPassword($password)
    {
        $this->_password = $password;
    }

    
    public function getPassword()
    {
        return $this->_password;
    }

    
    public function setAuthMode($mode)
    {
        $this->_auth_mode = $mode;
    }

    
    public function getAuthMode()
    {
        return $this->_auth_mode;
    }

    
    public function getHandledKeyword()
    {
        return 'AUTH';
    }

    
    public function setKeywordParams(array $parameters)
    {
        $this->_esmtpParams = $parameters;
    }

    
    public function afterEhlo(Swift_Transport_SmtpAgent $agent)
    {
        if ($this->_username) {
            $count = 0;
            foreach ($this->_getAuthenticatorsForAgent() as $authenticator) {
                if (in_array(strtolower($authenticator->getAuthKeyword()),
                    array_map('strtolower', $this->_esmtpParams))) {
                    ++$count;
                    if ($authenticator->authenticate($agent, $this->_username, $this->_password)) {
                        return;
                    }
                }
            }
            throw new Swift_TransportException(
                'Failed to authenticate on SMTP server with username "'.
                $this->_username.'" using '.$count.' possible authenticators'
                );
        }
    }

    
    public function getMailParams()
    {
        return array();
    }

    
    public function getRcptParams()
    {
        return array();
    }

    
    public function onCommand(Swift_Transport_SmtpAgent $agent, $command, $codes = array(), &$failedRecipients = null, &$stop = false)
    {
    }

    
    public function getPriorityOver($esmtpKeyword)
    {
        return 0;
    }

    
    public function exposeMixinMethods()
    {
        return array('setUsername', 'getUsername', 'setPassword', 'getPassword', 'setAuthMode', 'getAuthMode');
    }

    
    public function resetState()
    {
    }

    
    protected function _getAuthenticatorsForAgent()
    {
        if (!$mode = strtolower($this->_auth_mode)) {
            return $this->_authenticators;
        }

        foreach ($this->_authenticators as $authenticator) {
            if (strtolower($authenticator->getAuthKeyword()) == $mode) {
                return array($authenticator);
            }
        }

        throw new Swift_TransportException('Auth mode '.$mode.' is invalid');
    }
}
