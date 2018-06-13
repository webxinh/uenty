<?php

namespace Faker\Provider\pt_PT;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'PT', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
