<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_ExceptionMessageRegExp extends PHPUnit_Framework_Constraint
{
    
    protected $expectedMessageRegExp;

    
    public function __construct($expected)
    {
        parent::__construct();
        $this->expectedMessageRegExp = $expected;
    }

    
    protected function matches($other)
    {
        $match = PHPUnit_Util_Regex::pregMatchSafe($this->expectedMessageRegExp, $other->getMessage());

        if (false === $match) {
            throw new PHPUnit_Framework_Exception(
                "Invalid expected exception message regex given: '{$this->expectedMessageRegExp}'"
            );
        }

        return 1 === $match;
    }

    
    protected function failureDescription($other)
    {
        return sprintf(
            "exception message '%s' matches '%s'",
            $other->getMessage(),
            $this->expectedMessageRegExp
        );
    }

    
    public function toString()
    {
        return 'exception message matches ';
    }
}
