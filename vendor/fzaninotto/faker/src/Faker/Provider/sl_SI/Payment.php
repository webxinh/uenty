<?php

namespace Faker\Provider\sl_SI;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'SI', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
