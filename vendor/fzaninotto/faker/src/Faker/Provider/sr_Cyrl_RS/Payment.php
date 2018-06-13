<?php

namespace Faker\Provider\sr_Cyrl_RS;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'RS', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
