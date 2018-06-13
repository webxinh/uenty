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


class DateTimeComparator extends ObjectComparator
{
    
    public function accepts($expected, $actual)
    {
        return ($expected instanceof \DateTime || $expected instanceof \DateTimeInterface) &&
            ($actual instanceof \DateTime || $actual instanceof \DateTimeInterface);
    }

    
    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false, array &$processed = array())
    {
        $delta = new \DateInterval(sprintf('PT%sS', abs($delta)));

        $expectedLower = clone $expected;
        $expectedUpper = clone $expected;

        if ($actual < $expectedLower->sub($delta) ||
            $actual > $expectedUpper->add($delta)) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                $this->dateTimeToString($expected),
                $this->dateTimeToString($actual),
                false,
                'Failed asserting that two DateTime objects are equal.'
            );
        }
    }

    
    private function dateTimeToString($datetime)
    {
        $string = $datetime->format('Y-m-d\TH:i:s.uO');

        return $string ? $string : 'Invalid DateTimeInterface object';
    }
}
