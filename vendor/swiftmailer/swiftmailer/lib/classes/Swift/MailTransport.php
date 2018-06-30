<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_MailTransport extends Swift_Transport_MailTransport
{
    
    public function __construct($extraParams = '-f%s')
    {
        call_user_func_array(
            array($this, 'Swift_Transport_MailTransport::__construct'),
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('transport.mail')
            );

        $this->setExtraParams($extraParams);
    }

    
    public static function newInstance($extraParams = '-f%s')
    {
        return new self($extraParams);
    }
}