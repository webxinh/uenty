<?php

namespace Faker\Provider\it_CH;

class Address extends \Faker\Provider\it_IT\Address
{
    protected static $buildingNumber = array('###', '##', '#', '#a', '#b', '#c');

    protected static $streetPrefix = array('Piazza', 'Strada', 'Via', 'Borgo', 'Contrada', 'Rotonda', 'Incrocio');

    protected static $postcode = array('####');

    
    protected static $cityNames = array(
        'Aarau', 'Adliswil', 'Aesch', 'Affoltern am Albis', 'Allschwil', 'Altstätten', 'Amriswil', 'Arbon', 'Arth',
        'Baar', 'Baden', 'Basilea', 'Bassersdorf', 'Bellinzona', 'Belp', 'Berna', 'Bienne', 'Binningen', 'Birsfelden', 'Briga-Glis', 'Brugg', 'Buchs', 'Bulle', 'Burgdorf', 'Bülach',
        'Carouge', 'Cham', 'Chêne-Bougeries', 'Coira',
        'Davos', 'Delémont', 'Dietikon', 'Dübendorf', 'Ebikon',
        'Ecublens', 'Einsiedeln', 'Emmen',
        'Frauenfeld', 'Freienbach', 'Friburgo',
        'Ginevra', 'Gland', 'Gossau', 'Grenchen',
        'Herisau', 'Hinwil', 'Horgen', 'Horw',
        'Illnau-Effretikon', 'Ittigen',
        'Kloten', 'Kreuzlingen', 'Kriens', 'Köniz', 'Küsnacht', 'Küssnacht',
        'La Chaux-de-Fonds', 'La Tour-de-Peilz', 'Lancy', 'Langenthal', 'Le Grand-Saconnex', 'Le Locle', 'Liestal', 'Locarno', 'Losanna', 'Lucerna', 'Lugano', 'Lyss',
        'Martigny', 'Meilen', 'Mendrisio', 'Meyrin', 'Monthey', 'Montreux', 'Morges', 'Muri bei Bern', 'Muttenz', 'Männedorf', 'Möhlin', 'Münchenstein', 'Münsingen',
        'Neuchâtel', 'Neuhausen am Rheinfall', 'Nyon',
        'Oberwil', 'Oftringen', 'Olten', 'Onex', 'Opfikon', 'Ostermundigen',
        'Pfäffikon', 'Pratteln', 'Prilly', 'Pully',
        'Rapperswil-Jona', 'Regensdorf', 'Reinach', 'Renens', 'Rheinfelden', 'Richterswil', 'Riehen', 'Rüti',
        'San Gallo', 'Schlieren', 'Sciaffusa', 'Sierre', 'Sion', 'Soletta', 'Spiez', 'Spreitenbach', 'Steffisburg', 'Stäfa', 'Svitto',
        'Thalwil', 'Thun', 'Thônex',
        'Uster', 'Uzwil',
        'Val-de-Travers', 'Vernier', 'Versoix', 'Vevey', 'Veyrier', 'Villars-sur-Glâne', 'Volketswil',
        'Wallisellen', 'Weinfelden', 'Wettingen', 'Wetzikon', 'Wil', 'Winterthur', 'Wohlen', 'Worb', 'Wädenswil',
        'Yverdon-les-Bains',
        'Zofingen', 'Zollikon', 'Zugo', 'Zurigo'
    );

    
    protected static $canton = array(
        array('AG' => 'Argovia'),
        array('AI' => 'Appenzello Interno'),
        array('AR' => 'Appenzello Esterno'),
        array('BE' => 'Berna'),
        array('BL' => 'Basilea Campagna'),
        array('BS' => 'Basilea Città'),
        array('FR' => 'Friburgo'),
        array('GE' => 'Ginevra'),
        array('GL' => 'Glarona'),
        array('GR' => 'Grigioni'),
        array('JU' => 'Giura'),
        array('LU' => 'Lucerna'),
        array('NE' => 'Neuchâtel'),
        array('NW' => 'Nidvaldo'),
        array('OW' => 'Obvaldo'),
        array('SG' => 'San Gallo'),
        array('SH' => 'Sciaffusa'),
        array('SO' => 'Soletta'),
        array('SZ' => 'Svitto'),
        array('TG' => 'Turgovia'),
        array('TI' => 'Ticino'),
        array('UR' => 'Uri'),
        array('VD' => 'Vaud'),
        array('VS' => 'Vallese'),
        array('ZG' => 'Zugo'),
        array('ZH' => 'Zurigo')
    );

    protected static $cityFormats = array(
        '{{cityName}}',
    );

    protected static $streetNameFormats = array(
        '{{streetSuffix}} {{firstName}}',
        '{{streetSuffix}} {{lastName}}'
    );

    protected static $streetAddressFormats = array(
        '{{streetName}} {{buildingNumber}}',
    );
    protected static $addressFormats = array(
        "{{streetAddress}}\n{{postcode}} {{city}}",
    );

    
    public static function streetPrefix()
    {
        return static::randomElement(static::$streetPrefix);
    }

    
    public function cityName()
    {
        return static::randomElement(static::$cityNames);
    }

    
    public static function canton()
    {
        return static::randomElement(static::$canton);
    }

    
    public static function cantonShort()
    {
        $canton = static::canton();
        return key($canton);
    }

    
    public static function cantonName()
    {
        $canton = static::canton();
        return current($canton);
    }
}
