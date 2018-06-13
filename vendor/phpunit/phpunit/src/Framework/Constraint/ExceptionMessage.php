<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_ExceptionMessage extends PHPUnit_Framework_Constraint
{
    
    protected $expectedMessage;

    
    public function __construct($expected)
    {
        parent::__construct();
        $this->expectedMessage = $expected;
    }

    
    protected function matches($other)
    {
        return strpos($other->getMessage(), $this->expectedMessage) !== false;
    }

    
    protected function failureDescription($other)
    {
        return sprintf(
            "exception message '%s' contains '%s'",
            $other->getMessage(),
            $this->expectedMessage
        );
    }

    
    public function toString()
    {
        return 'exception message contains ';
    }
}
