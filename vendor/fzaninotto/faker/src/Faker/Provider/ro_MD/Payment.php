<?php

namespace Faker\Provider\ro_MD;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'MD', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
