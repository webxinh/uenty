<?php


namespace aabc\base;


class InvalidRouteException extends UserException
{
    
    public function getName()
    {
        return 'Invalid Route';
    }
}
