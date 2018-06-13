<?php

namespace Faker\Provider\da_DK;


class Company extends \Faker\Provider\Company
{
    
    protected static $formats = array(
        '{{lastName}} {{companySuffix}}',
        '{{lastName}} {{companySuffix}}',
        '{{lastName}} {{companySuffix}}',
        '{{firstname}} {{lastName}} {{companySuffix}}',
        '{{middleName}} {{companySuffix}}',
        '{{middleName}} {{companySuffix}}',
        '{{middleName}} {{companySuffix}}',
        '{{firstname}} {{middleName}} {{companySuffix}}',
        '{{lastName}} & {{lastName}} {{companySuffix}}',
        '{{lastName}} og {{lastName}} {{companySuffix}}',
        '{{lastName}} & {{lastName}} {{companySuffix}}',
        '{{lastName}} og {{lastName}} {{companySuffix}}',
        '{{middleName}} & {{middleName}} {{companySuffix}}',
        '{{middleName}} og {{middleName}} {{companySuffix}}',
        '{{middleName}} & {{lastName}}',
        '{{middleName}} og {{lastName}}',
    );

    
    protected static $companySuffix = array('ApS', 'A/S', 'I/S', 'K/S');

    
    protected static $cvrFormat = '%#######';

    
    protected static $pFormat = '%#########';

    
    public static function cvr()
    {
        return static::numerify(static::$cvrFormat);
    }

    
    public static function p()
    {
        return static::numerify(static::$pFormat);
    }
}
