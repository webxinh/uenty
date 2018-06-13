<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Util_Log_JSON extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    
    protected $currentTestSuiteName = '';

    
    protected $currentTestName = '';

    
    protected $currentTestPass = true;

    
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeCase(
            'error',
            $time,
            PHPUnit_Util_Filter::getFilteredStacktrace($e, false),
            PHPUnit_Framework_TestFailure::exceptionToString($e),
            $test
        );

        $this->currentTestPass = false;
    }

    
    public function addWarning(PHPUnit_Framework_Test $test, PHPUnit_Framework_Warning $e, $time)
    {
        $this->writeCase(
            'warning',
            $time,
            PHPUnit_Util_Filter::getFilteredStacktrace($e, false),
            PHPUnit_Framework_TestFailure::exceptionToString($e),
            $test
        );

        $this->currentTestPass = false;
    }

    
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->writeCase(
            'fail',
            $time,
            PHPUnit_Util_Filter::getFilteredStacktrace($e, false),
            PHPUnit_Framework_TestFailure::exceptionToString($e),
            $test
        );

        $this->currentTestPass = false;
    }

    
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeCase(
            'error',
            $time,
            PHPUnit_Util_Filter::getFilteredStacktrace($e, false),
            'Incomplete Test: ' . $e->getMessage(),
            $test
        );

        $this->currentTestPass = false;
    }

    
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeCase(
            'error',
            $time,
            PHPUnit_Util_Filter::getFilteredStacktrace($e, false),
            'Risky Test: ' . $e->getMessage(),
            $test
        );

        $this->currentTestPass = false;
    }

    
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeCase(
            'error',
            $time,
            PHPUnit_Util_Filter::getFilteredStacktrace($e, false),
            'Skipped Test: ' . $e->getMessage(),
            $test
        );

        $this->currentTestPass = false;
    }

    
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->currentTestSuiteName = $suite->getName();
        $this->currentTestName      = '';

        $this->write(
            [
            'event' => 'suiteStart',
            'suite' => $this->currentTestSuiteName,
            'tests' => count($suite)
            ]
        );
    }

    
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->currentTestSuiteName = '';
        $this->currentTestName      = '';
    }

    
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->currentTestName = PHPUnit_Util_Test::describe($test);
        $this->currentTestPass = true;

        $this->write(
            [
            'event' => 'testStart',
            'suite' => $this->currentTestSuiteName,
            'test'  => $this->currentTestName
            ]
        );
    }

    
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if ($this->currentTestPass) {
            $this->writeCase('pass', $time, [], '', $test);
        }
    }

    
    protected function writeCase($status, $time, array $trace = [], $message = '', $test = null)
    {
        $output = '';
        // take care of TestSuite producing error (e.g. by running into exception) as TestSuite doesn't have hasOutput
        if ($test !== null && method_exists($test, 'hasOutput') && $test->hasOutput()) {
            $output = $test->getActualOutput();
        }
        $this->write(
            [
            'event'   => 'test',
            'suite'   => $this->currentTestSuiteName,
            'test'    => $this->currentTestName,
            'status'  => $status,
            'time'    => $time,
            'trace'   => $trace,
            'message' => PHPUnit_Util_String::convertToUtf8($message),
            'output'  => $output,
            ]
        );
    }

    
    public function write($buffer)
    {
        array_walk_recursive($buffer, function (&$input) {
            if (is_string($input)) {
                $input = PHPUnit_Util_String::convertToUtf8($input);
            }
        });

        parent::write(json_encode($buffer, JSON_PRETTY_PRINT));
    }
}
