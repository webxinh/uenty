<?php

namespace Faker\Provider\kk_KZ;

class Payment extends \Faker\Provider\Payment
{

    protected static $banks = array(
        'Қазкоммерцбанк',
        'Халық Банкі',
    );

    
    public static function bank()
    {
        return static::randomElement(static::$banks);
    }

    
    public static function bankAccountNumber($prefix = '', $countryCode = 'KZ', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
