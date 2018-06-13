<?php


namespace aabc\base;


class UnknownClassException extends Exception
{
    
    public function getName()
    {
        return 'Unknown Class';
    }
}
