<?php

namespace Faker\Provider\is_IS;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'IS', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
