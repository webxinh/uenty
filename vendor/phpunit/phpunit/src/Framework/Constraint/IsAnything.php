<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_IsAnything extends PHPUnit_Framework_Constraint
{
    
    public function evaluate($other, $description = '', $returnResult = false)
    {
        return $returnResult ? true : null;
    }

    
    public function toString()
    {
        return 'is anything';
    }

    
    public function count()
    {
        return 0;
    }
}
