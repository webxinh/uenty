<?php
namespace Codeception\PHPUnit;

use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_Test;
use PHPUnit_Runner_BaseTestRunner;

class ResultPrinter extends \PHPUnit_Util_TestDox_ResultPrinter
{
    
    public function addError(PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_ERROR;
        $this->failed++;
    }

    
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE;
        $this->failed++;
    }

    
    public function addIncompleteTest(PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE;
        $this->incomplete++;
    }

    
    public function addRiskyTest(PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_RISKY;
        $this->risky++;
    }

    
    public function addSkippedTest(PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED;
        $this->skipped++;
    }

    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->testStatus = PHPUnit_Runner_BaseTestRunner::STATUS_PASSED;
    }
}
