<?php

namespace Faker\Provider\da_DK;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'DK', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
