<?php


namespace aabc\base;


class UnknownPropertyException extends Exception
{
    
    public function getName()
    {
        return 'Unknown Property';
    }
}
