<?php

namespace Faker\Provider\es_ES;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'ES', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
