<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_IsEmpty extends PHPUnit_Framework_Constraint
{
    
    protected function matches($other)
    {
        if ($other instanceof Countable) {
            return count($other) === 0;
        }

        return empty($other);
    }

    
    public function toString()
    {
        return 'is empty';
    }

    
    protected function failureDescription($other)
    {
        $type = gettype($other);

        return sprintf(
            '%s %s %s',
            $type[0] == 'a' || $type[0] == 'o' ? 'an' : 'a',
            $type,
            $this->toString()
        );
    }
}
