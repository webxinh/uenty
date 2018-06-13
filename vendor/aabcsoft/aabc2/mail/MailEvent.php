<?php


namespace aabc\mail;

use aabc\base\Event;


class MailEvent extends Event
{
    
    public $message;
    
    public $isSuccessful;
    
    public $isValid = true;
}
