<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_IsIdentical extends PHPUnit_Framework_Constraint
{
    
    const EPSILON = 0.0000000001;

    
    protected $value;

    
    public function __construct($value)
    {
        parent::__construct();
        $this->value = $value;
    }

    
    public function evaluate($other, $description = '', $returnResult = false)
    {
        if (is_double($this->value) && is_double($other) &&
            !is_infinite($this->value) && !is_infinite($other) &&
            !is_nan($this->value) && !is_nan($other)) {
            $success = abs($this->value - $other) < self::EPSILON;
        } else {
            $success = $this->value === $other;
        }

        if ($returnResult) {
            return $success;
        }

        if (!$success) {
            $f = null;

            // if both values are strings, make sure a diff is generated
            if (is_string($this->value) && is_string($other)) {
                $f = new SebastianBergmann\Comparator\ComparisonFailure(
                    $this->value,
                    $other,
                    $this->value,
                    $other
                );
            }

            $this->fail($other, $description, $f);
        }
    }

    
    protected function failureDescription($other)
    {
        if (is_object($this->value) && is_object($other)) {
            return 'two variables reference the same object';
        }

        if (is_string($this->value) && is_string($other)) {
            return 'two strings are identical';
        }

        return parent::failureDescription($other);
    }

    
    public function toString()
    {
        if (is_object($this->value)) {
            return 'is identical to an object of class "' .
                   get_class($this->value) . '"';
        } else {
            return 'is identical to ' .
                   $this->exporter->export($this->value);
        }
    }
}
