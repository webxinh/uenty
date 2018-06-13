<?php

namespace Faker\Provider\ro_RO;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'RO', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
