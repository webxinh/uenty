<?php

namespace Faker\Provider\it_IT;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'IT', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
