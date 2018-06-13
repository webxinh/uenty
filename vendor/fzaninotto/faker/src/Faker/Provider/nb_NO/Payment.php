<?php

namespace Faker\Provider\nb_NO;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'NO', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
