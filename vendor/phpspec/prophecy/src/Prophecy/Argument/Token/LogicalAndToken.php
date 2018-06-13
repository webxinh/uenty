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


class LogicalAndToken implements TokenInterface
{
    private $tokens = array();

    
    public function __construct(array $arguments)
    {
        foreach ($arguments as $argument) {
            if (!$argument instanceof TokenInterface) {
                $argument = new ExactValueToken($argument);
            }
            $this->tokens[] = $argument;
        }
    }

    
    public function scoreArgument($argument)
    {
        if (0 === count($this->tokens)) {
            return false;
        }

        $maxScore = 0;
        foreach ($this->tokens as $token) {
            $score = $token->scoreArgument($argument);
            if (false === $score) {
                return false;
            }
            $maxScore = max($score, $maxScore);
        }

        return $maxScore;
    }

    
    public function isLast()
    {
        return false;
    }

    
    public function __toString()
    {
        return sprintf('bool(%s)', implode(' AND ', $this->tokens));
    }
}
