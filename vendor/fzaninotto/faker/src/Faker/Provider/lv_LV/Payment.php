<?php

namespace Faker\Provider\lv_LV;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'LV', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
