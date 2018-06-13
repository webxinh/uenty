<?php

namespace Faker\Provider\me_ME;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'ME', $length = '18')
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
