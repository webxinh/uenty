<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_IsEqual extends PHPUnit_Framework_Constraint
{
    
    protected $value;

    
    protected $delta = 0.0;

    
    protected $maxDepth = 10;

    
    protected $canonicalize = false;

    
    protected $ignoreCase = false;

    
    protected $lastFailure;

    
    public function __construct($value, $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        parent::__construct();

        if (!is_numeric($delta)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'numeric');
        }

        if (!is_int($maxDepth)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(3, 'integer');
        }

        if (!is_bool($canonicalize)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(4, 'boolean');
        }

        if (!is_bool($ignoreCase)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(5, 'boolean');
        }

        $this->value        = $value;
        $this->delta        = $delta;
        $this->maxDepth     = $maxDepth;
        $this->canonicalize = $canonicalize;
        $this->ignoreCase   = $ignoreCase;
    }

    
    public function evaluate($other, $description = '', $returnResult = false)
    {
        // If $this->value and $other are identical, they are also equal.
        // This is the most common path and will allow us to skip
        // initialization of all the comparators.
        if ($this->value === $other) {
            return true;
        }

        $comparatorFactory = SebastianBergmann\Comparator\Factory::getInstance();

        try {
            $comparator = $comparatorFactory->getComparatorFor(
                $this->value,
                $other
            );

            $comparator->assertEquals(
                $this->value,
                $other,
                $this->delta,
                $this->canonicalize,
                $this->ignoreCase
            );
        } catch (SebastianBergmann\Comparator\ComparisonFailure $f) {
            if ($returnResult) {
                return false;
            }

            throw new PHPUnit_Framework_ExpectationFailedException(
                trim($description . "\n" . $f->getMessage()),
                $f
            );
        }

        return true;
    }

    
    public function toString()
    {
        $delta = '';

        if (is_string($this->value)) {
            if (strpos($this->value, "\n") !== false) {
                return 'is equal to <text>';
            } else {
                return sprintf(
                    'is equal to <string:%s>',
                    $this->value
                );
            }
        } else {
            if ($this->delta != 0) {
                $delta = sprintf(
                    ' with delta <%F>',
                    $this->delta
                );
            }

            return sprintf(
                'is equal to %s%s',
                $this->exporter->export($this->value),
                $delta
            );
        }
    }
}
