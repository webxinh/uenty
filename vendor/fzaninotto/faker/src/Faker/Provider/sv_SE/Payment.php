<?php

namespace Faker\Provider\sv_SE;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'SE', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
