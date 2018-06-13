<?php

namespace Faker\Provider\cs_CZ;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'CZ', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
