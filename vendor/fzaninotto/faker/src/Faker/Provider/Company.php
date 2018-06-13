<?php

namespace Faker\Provider;

class Company extends Base
{
    protected static $formats = array(
        '{{lastName}} {{companySuffix}}',
    );

    protected static $companySuffix = array('Ltd');

    protected static $jobTitleFormat = array(
        '{{word}}',
    );

    
    public function company()
    {
        $format = static::randomElement(static::$formats);

        return $this->generator->parse($format);
    }

    
    public static function companySuffix()
    {
        return static::randomElement(static::$companySuffix);
    }

    
    public function jobTitle()
    {
        $format = static::randomElement(static::$jobTitleFormat);

        return $this->generator->parse($format);
    }
}
