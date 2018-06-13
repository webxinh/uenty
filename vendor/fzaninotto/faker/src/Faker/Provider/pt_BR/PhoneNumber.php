<?php

namespace Faker\Provider\pt_BR;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{

    protected static $landlineFormats = array('2###-####', '3###-####');

    protected static $cellphoneFormats = array('7###-####', '8###-####', '9###-####');

    
    protected static $ninthDigitAreaCodes = array(
        11, 12, 13, 14, 15, 16, 17, 18, 19, // aug/2013
        21, 22, 24, 27, 28, // oct/2013
        91, 92, 93, 94, 95, 96, 97, 98, 99, // nov/2014
        81, 82, 83, 84, 85, 86, 87, 88, 89, // may/2015
        31, 32, 33, 34, 35, 37, 38, 71, 73, 74, 75, 77, 79, // oct/2015
        //41, 42, 43, 44, 45, 46, 47, 48, 49, 51, 53, 54, 55, 61, 62, 63, 64, 65, 66, 67, 68, 69 //by dec/2016
    );

    
    public static function areaCode()
    {
        return static::randomDigitNotNull().static::randomDigitNotNull();
    }

    
    public static function cellphone($formatted = true, $area = false)
    {
        $number = static::numerify(static::randomElement(static::$cellphoneFormats));

        if ($area === true || in_array($area, static::$ninthDigitAreaCodes)) {
            $number = "9$number";
        }

        if (!$formatted) {
            $number = strtr($number, array('-' => ''));
        }

        return $number;
    }

    
    public static function landline($formatted = true)
    {
        $number = static::numerify(static::randomElement(static::$landlineFormats));

        if (!$formatted) {
            $number = strtr($number, array('-' => ''));
        }

        return $number;
    }

    
    public static function phone($formatted = true)
    {
        $options = static::randomElement(array(
            array('cellphone', false),
            array('cellphone', true),
            array('landline', null),
        ));

        return call_user_func("static::{$options[0]}", $formatted, $options[1]);
    }

    
    protected static function anyPhoneNumber($type, $formatted = true)
    {
        $area   = static::areaCode();
        $number = ($type == 'cellphone')?
            static::cellphone($formatted, in_array($area, static::$ninthDigitAreaCodes)) :
            static::landline($formatted);

        return $formatted? "($area) $number" : $area.$number;
    }

    
    public static function cellphoneNumber($formatted = true)
    {
        return static::anyPhoneNumber('cellphone', $formatted);
    }

    
    public static function landlineNumber($formatted = true)
    {
        return static::anyPhoneNumber('landline', $formatted);
    }

    
    public function phoneNumber()
    {
        $method = static::randomElement(array('cellphoneNumber', 'landlineNumber'));
        return call_user_func("static::$method", true);
    }

    
    public static function phoneNumberCleared()
    {
        $method = static::randomElement(array('cellphoneNumber', 'landlineNumber'));
        return call_user_func("static::$method", false);
    }
}
