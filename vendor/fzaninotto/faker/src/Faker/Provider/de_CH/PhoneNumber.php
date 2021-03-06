<?php

namespace Faker\Provider\de_CH;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{
    protected static $formats = array(
        '+41 (0)## ### ## ##',
        '+41(0)#########',
        '+41 ## ### ## ##',
        '0#########',
        '0## ### ## ##',
    );

    
    protected static $mobileFormats = array(
        // Local
        '075 ### ## ##',
        '075#######',
        '076 ### ## ##',
        '076#######',
        '077 ### ## ##',
        '077#######',
        '078 ### ## ##',
        '078#######',
        '079 ### ## ##',
        '079#######',
    );

    
    public static function mobileNumber()
    {
        return static::numerify(static::randomElement(static::$mobileFormats));
    }
}
