<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_StringStartsWith extends PHPUnit_Framework_Constraint
{
    
    protected $prefix;

    
    public function __construct($prefix)
    {
        parent::__construct();
        $this->prefix = $prefix;
    }

    
    protected function matches($other)
    {
        return strpos($other, $this->prefix) === 0;
    }

    
    public function toString()
    {
        return 'starts with "' . $this->prefix . '"';
    }
}
