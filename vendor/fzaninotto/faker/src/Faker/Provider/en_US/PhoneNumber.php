<?php

namespace Faker\Provider\en_US;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{
    
    protected static $formats = array(
        // International format
        '+1-{{areaCode}}-{{exchangeCode}}-####',
        '+1 ({{areaCode}}) {{exchangeCode}}-####',
        '+1-{{areaCode}}-{{exchangeCode}}-####',
        '+1.{{areaCode}}.{{exchangeCode}}.####',
        '+1{{areaCode}}{{exchangeCode}}####',

        // Standard formats
        '{{areaCode}}-{{exchangeCode}}-####',
        '({{areaCode}}) {{exchangeCode}}-####',
        '1-{{areaCode}}-{{exchangeCode}}-####',
        '{{areaCode}}.{{exchangeCode}}.####',

        '{{areaCode}}-{{exchangeCode}}-####',
        '({{areaCode}}) {{exchangeCode}}-####',
        '1-{{areaCode}}-{{exchangeCode}}-####',
        '{{areaCode}}.{{exchangeCode}}.####',

        // Extensions
        '{{areaCode}}-{{exchangeCode}}-#### x###',
        '({{areaCode}}) {{exchangeCode}}-#### x###',
        '1-{{areaCode}}-{{exchangeCode}}-#### x###',
        '{{areaCode}}.{{exchangeCode}}.#### x###',

        '{{areaCode}}-{{exchangeCode}}-#### x####',
        '({{areaCode}}) {{exchangeCode}}-#### x####',
        '1-{{areaCode}}-{{exchangeCode}}-#### x####',
        '{{areaCode}}.{{exchangeCode}}.#### x####',

        '{{areaCode}}-{{exchangeCode}}-#### x#####',
        '({{areaCode}}) {{exchangeCode}}-#### x#####',
        '1-{{areaCode}}-{{exchangeCode}}-#### x#####',
        '{{areaCode}}.{{exchangeCode}}.#### x#####'
    );

    
    protected static $tollFreeAreaCodes = array(
        800, 844, 855, 866, 877, 888
    );
    protected static $tollFreeFormats = array(
        // Standard formats
        '{{tollFreeAreaCode}}-{{exchangeCode}}-####',
        '({{tollFreeAreaCode}}) {{exchangeCode}}-####',
        '1-{{tollFreeAreaCode}}-{{exchangeCode}}-####',
        '{{tollFreeAreaCode}}.{{exchangeCode}}.####',
    );

    public function tollFreeAreaCode()
    {
        return self::randomElement(static::$tollFreeAreaCodes);
    }

    public function tollFreePhoneNumber()
    {
        $format = self::randomElement(static::$tollFreeFormats);

        return self::numerify($this->generator->parse($format));
    }

    
    public static function areaCode()
    {
        $digits[] = self::numberBetween(2, 9);
        $digits[] = self::randomDigit();
        $digits[] = self::randomDigitNot($digits[1]);

        return join('', $digits);
    }

    
    public static function exchangeCode()
    {
        $digits[] = self::numberBetween(2, 9);
        $digits[] = self::randomDigit();

        if ($digits[1] === 1) {
            $digits[] = self::randomDigitNot(1);
        } else {
            $digits[] = self::randomDigit();
        }

        return join('', $digits);
    }
}
