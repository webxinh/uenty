<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_StringEndsWith extends PHPUnit_Framework_Constraint
{
    
    protected $suffix;

    
    public function __construct($suffix)
    {
        parent::__construct();
        $this->suffix = $suffix;
    }

    
    protected function matches($other)
    {
        return substr($other, 0 - strlen($this->suffix)) == $this->suffix;
    }

    
    public function toString()
    {
        return 'ends with "' . $this->suffix . '"';
    }
}
