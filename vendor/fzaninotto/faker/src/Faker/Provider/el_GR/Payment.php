<?php

namespace Faker\Provider\el_GR;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'GR', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
