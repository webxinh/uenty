<?php


namespace aabc\db;


class IntegrityException extends Exception
{
    
    public function getName()
    {
        return 'Integrity constraint violation';
    }
}
