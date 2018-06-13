<?php

namespace Faker\Provider\nl_BE;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'BE', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }

    
    public static function vat($spacedNationalPrefix = true)
    {
        $prefix = ($spacedNationalPrefix) ? "BE " : "BE";

        return sprintf("%s0%d", $prefix, self::randomNumber(9, true));
    }
}
