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


class AnyValueToken implements TokenInterface
{
    
    public function scoreArgument($argument)
    {
        return 3;
    }

    
    public function isLast()
    {
        return false;
    }

    
    public function __toString()
    {
        return '*';
    }
}
