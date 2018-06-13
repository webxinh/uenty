<?php
namespace Codeception\Util;


class Soap extends Xml
{
    public static function request()
    {
        return new XmlBuilder();
    }

    public static function response()
    {
        return new XmlBuilder();
    }
}
