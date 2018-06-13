<?php


namespace aabc\web;

use aabc\base\Event;


class UserEvent extends Event
{
    
    public $identity;
    
    public $cookieBased;
    
    public $duration;
    
    public $isValid = true;
}
