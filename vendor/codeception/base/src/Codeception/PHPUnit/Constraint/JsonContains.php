<?php

namespace Codeception\PHPUnit\Constraint;

use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\ArrayComparator;
use SebastianBergmann\Comparator\Factory;
use Codeception\Util\JsonArray;

class JsonContains extends \PHPUnit_Framework_Constraint
{
    
    protected $expected;

    public function __construct(array $expected)
    {
        parent::__construct();
        $this->expected = $expected;
    }

    
    protected function matches($other)
    {
        $jsonResponseArray = new JsonArray($other);

        if ($jsonResponseArray->containsArray($this->expected)) {
            return true;
        }

        $comparator = new ArrayComparator();
        $comparator->setFactory(new Factory);
        try {
            //$comparator->assertEquals(var_export($this->expected, true), var_export($jsonResponseArray->toArray(), true));
            $comparator->assertEquals($this->expected, $jsonResponseArray->toArray());
        } catch (ComparisonFailure $failure) {
            throw new \PHPUnit_Framework_ExpectationFailedException(
                "Response JSON does not contain the provided JSON\n",
                $failure
            );
        }
    }

    
    public function toString()
    {
        //unused
        return '';
    }

    protected function failureDescription($other)
    {
        //unused
        return '';
    }
}
