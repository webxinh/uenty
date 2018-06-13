<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_SmtpTransport extends Swift_Transport_EsmtpTransport
{
    
    public function __construct($host = 'localhost', $port = 25, $security = null)
    {
        call_user_func_array(
            array($this, 'Swift_Transport_EsmtpTransport::__construct'),
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('transport.smtp')
            );

        $this->setHost($host);
        $this->setPort($port);
        $this->setEncryption($security);
    }

    
    public static function newInstance($host = 'localhost', $port = 25, $security = null)
    {
        return new self($host, $port, $security);
    }
}
