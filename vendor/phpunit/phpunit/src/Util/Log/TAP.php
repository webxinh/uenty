<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Util_Log_TAP extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
    
    protected $testNumber = 0;

    
    protected $testSuiteLevel = 0;

    
    protected $testSuccessful = true;

    
    public function __construct($out = null)
    {
        parent::__construct($out);
        $this->write("TAP version 13\n");
    }

    
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeNotOk($test, 'Error');
    }

    
    public function addWarning(PHPUnit_Framework_Test $test, PHPUnit_Framework_Warning $e, $time)
    {
        $this->writeNotOk($test, 'Warning');
    }

    
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->writeNotOk($test, 'Failure');

        $message = explode(
            "\n",
            PHPUnit_Framework_TestFailure::exceptionToString($e)
        );

        $diagnostic = [
          'message'  => $message[0],
          'severity' => 'fail'
        ];

        if ($e instanceof PHPUnit_Framework_ExpectationFailedException) {
            $cf = $e->getComparisonFailure();

            if ($cf !== null) {
                $diagnostic['data'] = [
                  'got'      => $cf->getActual(),
                  'expected' => $cf->getExpected()
                ];
            }
        }

        $yaml = new Symfony\Component\Yaml\Dumper;

        $this->write(
            sprintf(
                "  ---\n%s  ...\n",
                $yaml->dump($diagnostic, 2, 2)
            )
        );
    }

    
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeNotOk($test, '', 'TODO Incomplete Test');
    }

    
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->write(
            sprintf(
                "ok %d - # RISKY%s\n",
                $this->testNumber,
                $e->getMessage() != '' ? ' ' . $e->getMessage() : ''
            )
        );

        $this->testSuccessful = false;
    }

    
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->write(
            sprintf(
                "ok %d - # SKIP%s\n",
                $this->testNumber,
                $e->getMessage() != '' ? ' ' . $e->getMessage() : ''
            )
        );

        $this->testSuccessful = false;
    }

    
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->testSuiteLevel++;
    }

    
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->testSuiteLevel--;

        if ($this->testSuiteLevel == 0) {
            $this->write(sprintf("1..%d\n", $this->testNumber));
        }
    }

    
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->testNumber++;
        $this->testSuccessful = true;
    }

    
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if ($this->testSuccessful === true) {
            $this->write(
                sprintf(
                    "ok %d - %s\n",
                    $this->testNumber,
                    PHPUnit_Util_Test::describe($test)
                )
            );
        }

        $this->writeDiagnostics($test);
    }

    
    protected function writeNotOk(PHPUnit_Framework_Test $test, $prefix = '', $directive = '')
    {
        $this->write(
            sprintf(
                "not ok %d - %s%s%s\n",
                $this->testNumber,
                $prefix != '' ? $prefix . ': ' : '',
                PHPUnit_Util_Test::describe($test),
                $directive != '' ? ' # ' . $directive : ''
            )
        );

        $this->testSuccessful = false;
    }

    
    private function writeDiagnostics(PHPUnit_Framework_Test $test)
    {
        if (!$test instanceof PHPUnit_Framework_TestCase) {
            return;
        }

        if (!$test->hasOutput()) {
            return;
        }

        foreach (explode("\n", trim($test->getActualOutput())) as $line) {
            $this->write(
                sprintf(
                    "# %s\n",
                    $line
                )
            );
        }
    }
}
