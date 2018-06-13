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


class NumericComparator extends ScalarComparator
{
    
    public function accepts($expected, $actual)
    {
        // all numerical values, but not if one of them is a double
        // or both of them are strings
        return is_numeric($expected) && is_numeric($actual) &&
               !(is_double($expected) || is_double($actual)) &&
               !(is_string($expected) && is_string($actual));
    }

    
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false)
    {
        if (is_infinite($actual) && is_infinite($expected)) {
            return;
        }

        if ((is_infinite($actual) xor is_infinite($expected)) ||
            (is_nan($actual) or is_nan($expected)) ||
            abs($actual - $expected) > $delta) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                '',
                '',
                false,
                sprintf(
                    'Failed asserting that %s matches expected %s.',
                    $this->exporter->export($actual),
                    $this->exporter->export($expected)
                )
            );
        }
    }
}
