<?php


namespace aabc\web;


class Cookie extends \aabc\base\Object
{
    
    public $name;
    
    public $value = '';
    
    public $domain = '';
    
    public $expire = 0;
    
    public $path = '/';
    
    public $secure = false;
    
    public $httpOnly = true;


    
    public function __toString()
    {
        return (string) $this->value;
    }
}
