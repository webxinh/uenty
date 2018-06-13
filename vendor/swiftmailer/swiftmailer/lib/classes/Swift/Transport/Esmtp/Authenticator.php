<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface Swift_Transport_Esmtp_Authenticator
{
    
    public function getAuthKeyword();

    
    public function authenticate(Swift_Transport_SmtpAgent $agent, $username, $password);
}
