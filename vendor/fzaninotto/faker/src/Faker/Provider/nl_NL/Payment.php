<?php

namespace Faker\Provider\nl_NL;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'NL', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
