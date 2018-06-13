<?php

namespace Faker\Provider\sk_SK;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'SK', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
