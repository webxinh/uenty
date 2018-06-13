<?php

namespace Faker\Provider;

class Biased extends Base
{
    
    public function biasedNumberBetween($min = 0, $max = 100, $function = 'sqrt')
    {
        do {
            $x = mt_rand() / mt_getrandmax();
            $y = mt_rand() / (mt_getrandmax() + 1);
        } while (call_user_func($function, $x) < $y);
        
        return floor($x * ($max - $min + 1) + $min);
    }

    
    protected static function unbiased($x)
    {
        return 1;
    }

    
    protected static function linearLow($x)
    {
        return 1 - $x;
    }

    
    protected static function linearHigh($x)
    {
        return $x;
    }
}
