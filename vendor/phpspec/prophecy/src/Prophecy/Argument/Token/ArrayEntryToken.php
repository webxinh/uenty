<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prophecy\Argument\Token;

use Prophecy\Exception\InvalidArgumentException;


class ArrayEntryToken implements TokenInterface
{
    
    private $key;
    
    private $value;

    
    public function __construct($key, $value)
    {
        $this->key = $this->wrapIntoExactValueToken($key);
        $this->value = $this->wrapIntoExactValueToken($value);
    }

    
    public function scoreArgument($argument)
    {
        if ($argument instanceof \Traversable) {
            $argument = iterator_to_array($argument);
        }

        if ($argument instanceof \ArrayAccess) {
            $argument = $this->convertArrayAccessToEntry($argument);
        }

        if (!is_array($argument) || empty($argument)) {
            return false;
        }

        $keyScores = array_map(array($this->key,'scoreArgument'), array_keys($argument));
        $valueScores = array_map(array($this->value,'scoreArgument'), $argument);
        $scoreEntry = function ($value, $key) {
            return $value && $key ? min(8, ($key + $value) / 2) : false;
        };

        return max(array_map($scoreEntry, $valueScores, $keyScores));
    }

    
    public function isLast()
    {
        return false;
    }

    
    public function __toString()
    {
        return sprintf('[..., %s => %s, ...]', $this->key, $this->value);
    }

    
    public function getKey()
    {
        return $this->key;
    }

    
    public function getValue()
    {
        return $this->value;
    }

    
    private function wrapIntoExactValueToken($value)
    {
        return $value instanceof TokenInterface ? $value : new ExactValueToken($value);
    }

    
    private function convertArrayAccessToEntry(\ArrayAccess $object)
    {
        if (!$this->key instanceof ExactValueToken) {
            throw new InvalidArgumentException(sprintf(
                'You can only use exact value tokens to match key of ArrayAccess object'.PHP_EOL.
                'But you used `%s`.',
                $this->key
            ));
        }

        $key = $this->key->getValue();

        return $object->offsetExists($key) ? array($key => $object[$key]) : array();
    }
}
