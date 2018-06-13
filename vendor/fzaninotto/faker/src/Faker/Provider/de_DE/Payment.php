<?php

namespace Faker\Provider\de_DE;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'DE', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
