<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prophecy;

use Prophecy\Argument\Token;


class Argument
{
    
    public static function exact($value)
    {
        return new Token\ExactValueToken($value);
    }

    
    public static function type($type)
    {
        return new Token\TypeToken($type);
    }

    
    public static function which($methodName, $value)
    {
        return new Token\ObjectStateToken($methodName, $value);
    }

    
    public static function that($callback)
    {
        return new Token\CallbackToken($callback);
    }

    
    public static function any()
    {
        return new Token\AnyValueToken;
    }

    
    public static function cetera()
    {
        return new Token\AnyValuesToken;
    }

    
    public static function allOf()
    {
        return new Token\LogicalAndToken(func_get_args());
    }

    
    public static function size($value)
    {
        return new Token\ArrayCountToken($value);
    }

    
    public static function withEntry($key, $value)
    {
        return new Token\ArrayEntryToken($key, $value);
    }

    
    public static function withEveryEntry($value)
    {
        return new Token\ArrayEveryEntryToken($value);
    }

    
    public static function containing($value)
    {
        return new Token\ArrayEntryToken(self::any(), $value);
    }

    
    public static function withKey($key)
    {
        return new Token\ArrayEntryToken($key, self::any());
    }

    
    public static function not($value)
    {
        return new Token\LogicalNotToken($value);
    }

    
    public static function containingString($value)
    {
        return new Token\StringContainsToken($value);
    }

    
    public static function is($value)
    {
        return new Token\IdenticalValueToken($value);
    }

    
    public static function approximate($value, $precision = 0)
    {
        return new Token\ApproximateValueToken($value, $precision);
    }
}
