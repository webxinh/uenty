<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_JsonMatches extends PHPUnit_Framework_Constraint
{
    
    protected $value;

    
    public function __construct($value)
    {
        parent::__construct();
        $this->value = $value;
    }

    
    protected function matches($other)
    {
        $decodedOther = json_decode($other);
        if (json_last_error()) {
            return false;
        }

        $decodedValue = json_decode($this->value);
        if (json_last_error()) {
            return false;
        }

        return $decodedOther == $decodedValue;
    }

    
    public function toString()
    {
        return sprintf(
            'matches JSON string "%s"',
            $this->value
        );
    }
}
