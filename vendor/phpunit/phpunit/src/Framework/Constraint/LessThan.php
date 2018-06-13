<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_LessThan extends PHPUnit_Framework_Constraint
{
    
    protected $value;

    
    public function __construct($value)
    {
        parent::__construct();
        $this->value = $value;
    }

    
    protected function matches($other)
    {
        return $this->value > $other;
    }

    
    public function toString()
    {
        return 'is less than ' . $this->exporter->export($this->value);
    }
}
