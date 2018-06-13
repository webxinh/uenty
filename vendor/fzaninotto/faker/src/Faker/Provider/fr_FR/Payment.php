<?php

namespace Faker\Provider\fr_FR;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'FR', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
