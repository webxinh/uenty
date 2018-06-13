<?php

namespace Faker\Provider\ka_GE;

class Payment extends \Faker\Provider\Payment
{

    
    protected static $banks = array(
        'ბანკი რესპუბლიკა',
        'თიბისი ბანკი',
        'საქართველოს ბანკი',
        'ლიბერთი ბანკი',
        'ბაზისბანკი',
        'ვითიბი ბანკი ჯორჯია',
        'ბანკი ქართუ',
        'პროკრედიტ ბანკი',
        'სილქ როუდ ბანკი ',
        'კაპიტალ ბანკი ',
        'აზერბაიჯანის საერთაშორისო ბანკი - საქართველო ',
        'ზირაათ ბანკის თბილისის ფილიალი ',
        'კავკასიის განვითარების ბანკი - საქართველო',
        'იშ ბანკი საქართველო',
        'პროგრეს ბანკი',
        'კორ სტანდარტ ბანკი',
        'ხალიკ ბანკი საქართველო ',
        'პაშა ბანკი საქართველო',
        'ფინკა ბანკი საქართველო',
    );

    
    public static function bank()
    {
        return static::randomElement(static::$banks);
    }

    
    public static function bankAccountNumber($prefix = '', $countryCode = 'GE', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
