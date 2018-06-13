<?php


namespace aabc\db;


class StaleObjectException extends Exception
{
    
    public function getName()
    {
        return 'Stale Object Exception';
    }
}
