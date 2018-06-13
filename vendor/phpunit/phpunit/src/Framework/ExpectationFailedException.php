<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_ExpectationFailedException extends PHPUnit_Framework_AssertionFailedError
{
    
    protected $comparisonFailure;

    public function __construct($message, SebastianBergmann\Comparator\ComparisonFailure $comparisonFailure = null, Exception $previous = null)
    {
        $this->comparisonFailure = $comparisonFailure;

        parent::__construct($message, 0, $previous);
    }

    
    public function getComparisonFailure()
    {
        return $this->comparisonFailure;
    }
}
