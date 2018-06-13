<?php


namespace aabc\i18n;

use aabc\base\Event;


class MissingTranslationEvent extends Event
{
    
    public $message;
    
    public $translatedMessage;
    
    public $category;
    
    public $language;
}
