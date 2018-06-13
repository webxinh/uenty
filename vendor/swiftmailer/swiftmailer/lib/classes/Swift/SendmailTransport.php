<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_SendmailTransport extends Swift_Transport_SendmailTransport
{
    
    public function __construct($command = '/usr/sbin/sendmail -bs')
    {
        call_user_func_array(
            array($this, 'Swift_Transport_SendmailTransport::__construct'),
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('transport.sendmail')
            );

        $this->setCommand($command);
    }

    
    public static function newInstance($command = '/usr/sbin/sendmail -bs')
    {
        return new self($command);
    }
}
