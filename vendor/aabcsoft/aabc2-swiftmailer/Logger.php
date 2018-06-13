<?php


namespace aabc\swiftmailer;

use Aabc;


class Logger implements \Swift_Plugins_Logger
{
    
    public function add($entry)
    {
        Aabc::info($entry, __METHOD__);
    }

    
    public function clear()
    {
        // do nothing
    }

    
    public function dump()
    {
        return '';
    }
}