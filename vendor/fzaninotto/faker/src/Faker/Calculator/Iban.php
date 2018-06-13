<?php

namespace Faker\Calculator;

class Iban
{
    
    public static function checksum($iban)
    {
        // Move first four digits to end and set checksum to '00'
        $checkString = substr($iban, 4) . substr($iban, 0, 2) . '00';

        // Replace all letters with their number equivalents
        $checkString = preg_replace_callback('/[A-Z]/', array('self','alphaToNumberCallback'), $checkString);

        // Perform mod 97 and subtract from 98
        $checksum = 98 - self::mod97($checkString);

        return str_pad($checksum, 2, '0', STR_PAD_LEFT);
    }

    
    private static function alphaToNumberCallback($match)
    {
        return self::alphaToNumber($match[0]);
    }

    
    public static function alphaToNumber($char)
    {
        return ord($char) - 55;
    }

    
    public static function mod97($number)
    {
        $checksum = (int)$number[0];
        for ($i = 1, $size = strlen($number); $i < $size; $i++) {
            $checksum = (10 * $checksum + (int) $number[$i]) % 97;
        }
        return $checksum;
    }

    
    public static function isValid($iban)
    {
        return self::checksum($iban) === substr($iban, 2, 2);
    }
}
