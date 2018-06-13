<?php
/*
 * This file is part of the Comparator package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\Comparator;


class DoubleComparator extends NumericComparator
{
    
    const EPSILON = 0.0000000001;

    
    public function accepts($expected, $actual)
    {
        return (is_double($expected) || is_double($actual)) && is_numeric($expected) && is_numeric($actual);
    }

    
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        if ($delta == 0) {
            $delta = self::EPSILON;
        }

        parent::assertEquals($expected, $actual, $delta, $canonicalize, $ignoreCase);
    }
}
