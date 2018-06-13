<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SebastianBergmann\Exporter\Exporter;


abstract class PHPUnit_Framework_Constraint implements Countable, PHPUnit_Framework_SelfDescribing
{
    protected $exporter;

    public function __construct()
    {
        $this->exporter = new Exporter;
    }

    
    public function evaluate($other, $description = '', $returnResult = false)
    {
        $success = false;

        if ($this->matches($other)) {
            $success = true;
        }

        if ($returnResult) {
            return $success;
        }

        if (!$success) {
            $this->fail($other, $description);
        }
    }

    
    protected function matches($other)
    {
        return false;
    }

    
    public function count()
    {
        return 1;
    }

    
    protected function fail($other, $description, SebastianBergmann\Comparator\ComparisonFailure $comparisonFailure = null)
    {
        $failureDescription = sprintf(
            'Failed asserting that %s.',
            $this->failureDescription($other)
        );

        $additionalFailureDescription = $this->additionalFailureDescription($other);

        if ($additionalFailureDescription) {
            $failureDescription .= "\n" . $additionalFailureDescription;
        }

        if (!empty($description)) {
            $failureDescription = $description . "\n" . $failureDescription;
        }

        throw new PHPUnit_Framework_ExpectationFailedException(
            $failureDescription,
            $comparisonFailure
        );
    }

    
    protected function additionalFailureDescription($other)
    {
        return '';
    }

    
    protected function failureDescription($other)
    {
        return $this->exporter->export($other) . ' ' . $this->toString();
    }
}
