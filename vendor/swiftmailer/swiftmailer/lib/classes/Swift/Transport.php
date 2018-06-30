<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface Swift_Transport
{
    
    public function isStarted();

    
    public function start();

    
    public function stop();

    
    public function send(Swift_Mime_Message $message, &$failedRecipients = null);

    
    public function registerPlugin(Swift_Events_EventListener $plugin);
}