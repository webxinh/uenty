<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_FileExists extends PHPUnit_Framework_Constraint
{
    
    protected function matches($other)
    {
        return file_exists($other);
    }

    
    protected function failureDescription($other)
    {
        return sprintf(
            'file "%s" exists',
            $other
        );
    }

    
    public function toString()
    {
        return 'file exists';
    }
}
