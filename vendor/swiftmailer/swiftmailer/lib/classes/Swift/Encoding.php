<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_Encoding
{
    
    public static function get7BitEncoding()
    {
        return self::_lookup('mime.7bitcontentencoder');
    }

    
    public static function get8BitEncoding()
    {
        return self::_lookup('mime.8bitcontentencoder');
    }

    
    public static function getQpEncoding()
    {
        return self::_lookup('mime.qpcontentencoder');
    }

    
    public static function getBase64Encoding()
    {
        return self::_lookup('mime.base64contentencoder');
    }

    // -- Private Static Methods

    private static function _lookup($key)
    {
        return Swift_DependencyContainer::getInstance()->lookup($key);
    }
}
