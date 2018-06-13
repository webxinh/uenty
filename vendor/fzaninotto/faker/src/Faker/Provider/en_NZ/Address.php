<?php

namespace Faker\Provider\en_NZ;

class Address extends \Faker\Provider\en_US\Address
{

    
    protected static $buildingNumber = array('#', '##', '###');

    
    protected static $streetSuffix = array(
        'Avenue', 'Close', 'Court', 'Crescent', 'Drive', 'Esplanade', 'Grove', 'Heights', 'Highway', 'Hill', 'Lane', 'Line', 'Mall', 'Parade', 'Place', 'Quay', 'Rise', 'Road', 'Square', 'Street', 'Terrace', 'Way'
    );

    
    protected static $citySuffix = array('ville', 'ston');

    
    protected static $cityFormats = array('{{firstName}}{{citySuffix}}');

    
    protected static $region = array(
        'Auckland', 'Bay of Plenty', 'Canterbury', 'Gisborne', 'Hawkes Bay', 'Manawatu-Whanganui', 'Marlborough', 'Nelson', 'Northland', 'Otago', 'Southland', 'Taranaki', 'Tasman', 'Waikato', 'Wellington', 'West Coast'
    );

    
    protected static $postcode = array('####');

    
    protected static $addressFormats = array('{{buildingNumber}} {{streetName}}, {{city}}, {{region}}, {{postcode}}');

    
    protected static $streetAddressFormats = array('{{buildingNumber}} {{streetName}}');

    
    public static function postcode()
    {
        return static::numerify(static::randomElement(static::$postcode));
    }

    
    public static function region()
    {
        return static::randomElement(static::$region);
    }
}
