<?php

namespace Faker\Provider\hu_HU;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'HU', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
