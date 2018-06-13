<?php


namespace aabc\base;


class InvalidConfigException extends Exception
{
    
    public function getName()
    {
        return 'Invalid Configuration';
    }
}
