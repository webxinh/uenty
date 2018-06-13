<?php

namespace Faker\Provider\tr_TR;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'TR', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
