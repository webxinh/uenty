<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface Swift_Events_TransportChangeListener extends Swift_Events_EventListener
{
    
    public function beforeTransportStarted(Swift_Events_TransportChangeEvent $evt);

    
    public function transportStarted(Swift_Events_TransportChangeEvent $evt);

    
    public function beforeTransportStopped(Swift_Events_TransportChangeEvent $evt);

    
    public function transportStopped(Swift_Events_TransportChangeEvent $evt);
}
