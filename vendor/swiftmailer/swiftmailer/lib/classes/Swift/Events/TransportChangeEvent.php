<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Events_TransportChangeEvent extends Swift_Events_EventObject
{
    
    public function getTransport()
    {
        return $this->getSource();
    }
}
