<?php

namespace Faker\Provider\en_IN;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{
    protected static $formats = array(
        '+91 ## ########',
        '+91 ### #######',
        '0## ########',
        '0### #######'
    );

    
    protected static $mobileFormats = array(
        '+91 9#########',
        '+91 8#########',
        '+91 7#########',
        '09#########',
        '08#########',
        '07#########'
    );

    
    public static function mobileNumber()
    {
        return static::numerify(static::randomElement(static::$mobileFormats));
    }
}
