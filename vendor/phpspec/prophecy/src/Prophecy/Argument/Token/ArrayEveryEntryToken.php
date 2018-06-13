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


class ArrayEveryEntryToken implements TokenInterface
{
    
    private $value;

    
    public function __construct($value)
    {
        if (!$value instanceof TokenInterface) {
            $value = new ExactValueToken($value);
        }

        $this->value = $value;
    }

    
    public function scoreArgument($argument)
    {
        if (!$argument instanceof \Traversable && !is_array($argument)) {
            return false;
        }

        $scores = array();
        foreach ($argument as $key => $argumentEntry) {
            $scores[] = $this->value->scoreArgument($argumentEntry);
        }

        if (empty($scores) || in_array(false, $scores, true)) {
            return false;
        }

        return array_sum($scores) / count($scores);
    }

    
    public function isLast()
    {
        return false;
    }

    
    public function __toString()
    {
        return sprintf('[%s, ..., %s]', $this->value, $this->value);
    }

    
    public function getValue()
    {
        return $this->value;
    }
}
