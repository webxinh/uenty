<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface Swift_Mime_Message extends Swift_Mime_MimeEntity
{
    
    public function generateId();

    
    public function setSubject($subject);

    
    public function getSubject();

    
    public function setDate($date);

    
    public function getDate();

    
    public function setReturnPath($address);

    
    public function getReturnPath();

    
    public function setSender($address, $name = null);

    
    public function getSender();

    
    public function setFrom($addresses, $name = null);

    
    public function getFrom();

    
    public function setReplyTo($addresses, $name = null);

    
    public function getReplyTo();

    
    public function setTo($addresses, $name = null);

    
    public function getTo();

    
    public function setCc($addresses, $name = null);

    
    public function getCc();

    
    public function setBcc($addresses, $name = null);

    
    public function getBcc();
}
