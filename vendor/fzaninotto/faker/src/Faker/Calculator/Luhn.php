<?php

namespace Faker\Calculator;


class Luhn
{
    
    private static function checksum($number)
    {
        $number = (string) $number;
        $length = strlen($number);
        $sum = 0;
        for ($i = $length - 1; $i >= 0; $i -= 2) {
            $sum += $number{$i};
        }
        for ($i = $length - 2; $i >= 0; $i -= 2) {
            $sum += array_sum(str_split($number{$i} * 2));
        }

        return $sum % 10;
    }

    
    public static function computeCheckDigit($partialNumber)
    {
        $checkDigit = self::checksum($partialNumber . '0');
        if ($checkDigit === 0) {
            return 0;
        }

        return (string) (10 - $checkDigit);
    }

    
    public static function isValid($number)
    {
        return self::checksum($number) === 0;
    }
}
