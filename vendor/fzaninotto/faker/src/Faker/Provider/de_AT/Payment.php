<?php

namespace Faker\Provider\de_AT;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'AT', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
