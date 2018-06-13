<?php


namespace aabc\base;


class NotSupportedException extends Exception
{
    
    public function getName()
    {
        return 'Not Supported';
    }
}
