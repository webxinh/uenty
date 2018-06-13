<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_LoadBalancedTransport extends Swift_Transport_LoadBalancedTransport
{
    
    public function __construct($transports = array())
    {
        call_user_func_array(
            array($this, 'Swift_Transport_LoadBalancedTransport::__construct'),
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('transport.loadbalanced')
            );

        $this->setTransports($transports);
    }

    
    public static function newInstance($transports = array())
    {
        return new self($transports);
    }
}
