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


class AnyValuesToken implements TokenInterface
{
    
    public function scoreArgument($argument)
    {
        return 2;
    }

    
    public function isLast()
    {
        return true;
    }

    
    public function __toString()
    {
        return '* [, ...]';
    }
}
