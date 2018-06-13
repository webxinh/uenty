<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Transport_Esmtp_Auth_XOAuth2Authenticator implements Swift_Transport_Esmtp_Authenticator
{
    
    public function getAuthKeyword()
    {
        return 'XOAUTH2';
    }

    
    public function authenticate(Swift_Transport_SmtpAgent $agent, $email, $token)
    {
        try {
            $param = $this->constructXOAuth2Params($email, $token);
            $agent->executeCommand('AUTH XOAUTH2 '.$param."\r\n", array(235));

            return true;
        } catch (Swift_TransportException $e) {
            $agent->executeCommand("RSET\r\n", array(250));

            return false;
        }
    }

    
    protected function constructXOAuth2Params($email, $token)
    {
        return base64_encode("user=$email\1auth=Bearer $token\1\1");
    }
}
