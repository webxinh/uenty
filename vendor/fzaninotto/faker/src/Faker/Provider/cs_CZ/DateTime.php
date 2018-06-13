<?php

namespace Faker\Provider\cs_CZ;


class DateTime extends \Faker\Provider\DateTime
{
    protected static $days = array(
        'neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'
    );
    protected static $months = array(
        'leden', 'únor', 'březen', 'duben', 'květen', 'červen', 'červenec',
        'srpen', 'září', 'říjen', 'listopad', 'prosinec'
    );
    protected static $monthsGenitive  = array(
        'ledna', 'února', 'března', 'dubna', 'května', 'června', 'července',
        'srpna', 'září', 'října', 'listopadu', 'prosince'
    );
    protected static $formattedDateFormat = array(
        '{{dayOfMonth}}. {{monthNameGenitive}} {{year}}',
    );

    public static function monthName($max = 'now')
    {
        return static::$months[parent::month($max) - 1];
    }

    public static function monthNameGenitive($max = 'now')
    {
        return static::$monthsGenitive[parent::month($max) - 1];
    }

    public static function dayOfWeek($max = 'now')
    {
        return static::$days[static::dateTime($max)->format('w')];
    }

    
    public static function dayOfMonth($max = 'now')
    {
        return static::dateTime($max)->format('j');
    }

    
    public function formattedDate()
    {
        $format = static::randomElement(static::$formattedDateFormat);

        return $this->generator->parse($format);
    }
}
