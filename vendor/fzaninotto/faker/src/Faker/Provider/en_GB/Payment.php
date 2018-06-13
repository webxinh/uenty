<?php

namespace Faker\Provider\en_GB;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'GB', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
