<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\CssSelector\Exception;

use Symfony\Component\CssSelector\Parser\Token;


class SyntaxErrorException extends ParseException
{
    
    public static function unexpectedToken($expectedValue, Token $foundToken)
    {
        return new self(sprintf('Expected %s, but %s found.', $expectedValue, $foundToken));
    }

    
    public static function pseudoElementFound($pseudoElement, $unexpectedLocation)
    {
        return new self(sprintf('Unexpected pseudo-element "::%s" found %s.', $pseudoElement, $unexpectedLocation));
    }

    
    public static function unclosedString($position)
    {
        return new self(sprintf('Unclosed/invalid string at %s.', $position));
    }

    
    public static function nestedNot()
    {
        return new self('Got nested ::not().');
    }

    
    public static function stringAsFunctionArgument()
    {
        return new self('String not allowed as function argument.');
    }
}
