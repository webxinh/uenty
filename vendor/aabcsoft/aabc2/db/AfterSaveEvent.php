<?php


namespace aabc\db;

use aabc\base\Event;


class AfterSaveEvent extends Event
{
    
    public $changedAttributes;
}
