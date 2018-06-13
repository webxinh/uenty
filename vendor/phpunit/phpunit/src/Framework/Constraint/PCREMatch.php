<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_PCREMatch extends PHPUnit_Framework_Constraint
{
    
    protected $pattern;

    
    public function __construct($pattern)
    {
        parent::__construct();
        $this->pattern = $pattern;
    }

    
    protected function matches($other)
    {
        return preg_match($this->pattern, $other) > 0;
    }

    
    public function toString()
    {
        return sprintf(
            'matches PCRE pattern "%s"',
            $this->pattern
        );
    }
}
