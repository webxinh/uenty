<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prophecy\Doubler;

use ReflectionClass;


class NameGenerator
{
    private static $counter = 1;

    
    public function name(ReflectionClass $class = null, array $interfaces)
    {
        $parts = array();

        if (null !== $class) {
            $parts[] = $class->getName();
        } else {
            foreach ($interfaces as $interface) {
                $parts[] = $interface->getShortName();
            }
        }

        if (!count($parts)) {
            $parts[] = 'stdClass';
        }

        return sprintf('Double\%s\P%d', implode('\\', $parts), self::$counter++);
    }
}
