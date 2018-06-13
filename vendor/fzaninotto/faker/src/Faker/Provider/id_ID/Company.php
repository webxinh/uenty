<?php

namespace Faker\Provider\id_ID;

class Company extends \Faker\Provider\Company
{
    protected static $formats = array(
        '{{companyPrefix}} {{lastName}}',
        '{{companyPrefix}} {{lastName}} {{lastName}}',
        '{{companyPrefix}} {{lastName}} {{companySuffix}}',
        '{{companyPrefix}} {{lastName}} {{lastName}} {{companySuffix}}',
    );

    
    protected static $companyPrefix = array('PT', 'CV', 'UD', 'PD', 'Perum');

    
    protected static $companySuffix = array('(Persero) Tbk', 'Tbk');

    
    public static function companyPrefix()
    {
        return static::randomElement(static::$companyPrefix);
    }

    
    public static function companySuffix()
    {
        return static::randomElement(static::$companySuffix);
    }
}
