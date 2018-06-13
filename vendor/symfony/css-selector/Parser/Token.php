<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\CssSelector\Parser;


class Token
{
    const TYPE_FILE_END = 'eof';
    const TYPE_DELIMITER = 'delimiter';
    const TYPE_WHITESPACE = 'whitespace';
    const TYPE_IDENTIFIER = 'identifier';
    const TYPE_HASH = 'hash';
    const TYPE_NUMBER = 'number';
    const TYPE_STRING = 'string';

    
    private $type;

    
    private $value;

    
    private $position;

    
    public function __construct($type, $value, $position)
    {
        $this->type = $type;
        $this->value = $value;
        $this->position = $position;
    }

    
    public function getType()
    {
        return $this->type;
    }

    
    public function getValue()
    {
        return $this->value;
    }

    
    public function getPosition()
    {
        return $this->position;
    }

    
    public function isFileEnd()
    {
        return self::TYPE_FILE_END === $this->type;
    }

    
    public function isDelimiter(array $values = array())
    {
        if (self::TYPE_DELIMITER !== $this->type) {
            return false;
        }

        if (empty($values)) {
            return true;
        }

        return in_array($this->value, $values);
    }

    
    public function isWhitespace()
    {
        return self::TYPE_WHITESPACE === $this->type;
    }

    
    public function isIdentifier()
    {
        return self::TYPE_IDENTIFIER === $this->type;
    }

    
    public function isHash()
    {
        return self::TYPE_HASH === $this->type;
    }

    
    public function isNumber()
    {
        return self::TYPE_NUMBER === $this->type;
    }

    
    public function isString()
    {
        return self::TYPE_STRING === $this->type;
    }

    
    public function __toString()
    {
        if ($this->value) {
            return sprintf('<%s "%s" at %s>', $this->type, $this->value, $this->position);
        }

        return sprintf('<%s at %s>', $this->type, $this->position);
    }
}
