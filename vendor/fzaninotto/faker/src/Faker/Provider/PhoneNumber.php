<?php

namespace Faker\Provider;

use Faker\Calculator\Luhn;

class PhoneNumber extends Base
{
    protected static $formats = array('###-###-###');

    
    public function phoneNumber()
    {
        return static::numerify($this->generator->parse(static::randomElement(static::$formats)));
    }

    
    public function e164PhoneNumber()
    {
        $formats = array('+#############');
        return static::numerify($this->generator->parse(static::randomElement($formats)));
    }

    
    public function imei()
    {
        $imei = (string) static::numerify('##############');
        $imei .= Luhn::computeCheckDigit($imei);
        return $imei;
    }
}
