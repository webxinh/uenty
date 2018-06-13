<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Exception as CodeCoverageException;
use SebastianBergmann\CodeCoverage\CoveredCodeNotExecutedException;
use SebastianBergmann\CodeCoverage\MissingCoversAnnotationException;
use SebastianBergmann\CodeCoverage\UnintentionallyCoveredCodeException;
use SebastianBergmann\ResourceOperations\ResourceOperations;


class PHPUnit_Framework_TestResult implements Countable
{
    
    protected $passed = [];

    
    protected $errors = [];

    
    protected $failures = [];

    
    protected $warnings = [];

    
    protected $notImplemented = [];

    
    protected $risky = [];

    
    protected $skipped = [];

    
    protected $listeners = [];

    
    protected $runTests = 0;

    
    protected $time = 0;

    
    protected $topTestSuite = null;

    
    protected $codeCoverage;

    
    protected $convertErrorsToExceptions = true;

    
    protected $stop = false;

    
    protected $stopOnError = false;

    
    protected $stopOnFailure = false;

    
    protected $stopOnWarning = false;

    
    protected $beStrictAboutTestsThatDoNotTestAnything = false;

    
    protected $beStrictAboutOutputDuringTests = false;

    
    protected $beStrictAboutTodoAnnotatedTests = false;

    
    protected $beStrictAboutResourceUsageDuringSmallTests = false;

    
    protected $enforceTimeLimit = false;

    
    protected $timeoutForSmallTests = 1;

    
    protected $timeoutForMediumTests = 10;

    
    protected $timeoutForLargeTests = 60;

    
    protected $stopOnRisky = false;

    
    protected $stopOnIncomplete = false;

    
    protected $stopOnSkipped = false;

    
    protected $lastTestFailed = false;

    
    private $registerMockObjectsFromTestArgumentsRecursively = false;

    
    public function addListener(PHPUnit_Framework_TestListener $listener)
    {
        $this->listeners[] = $listener;
    }

    
    public function removeListener(PHPUnit_Framework_TestListener $listener)
    {
        foreach ($this->listeners as $key => $_listener) {
            if ($listener === $_listener) {
                unset($this->listeners[$key]);
            }
        }
    }

    
    public function flushListeners()
    {
        foreach ($this->listeners as $listener) {
            if ($listener instanceof PHPUnit_Util_Printer) {
                $listener->flush();
            }
        }
    }

    
    public function addError(PHPUnit_Framework_Test $test, $t, $time)
    {
        if ($t instanceof PHPUnit_Framework_RiskyTest) {
            $this->risky[] = new PHPUnit_Framework_TestFailure($test, $t);
            $notifyMethod  = 'addRiskyTest';

            if ($test instanceof PHPUnit_Framework_TestCase) {
                $test->markAsRisky();
            }

            if ($this->stopOnRisky) {
                $this->stop();
            }
        } elseif ($t instanceof PHPUnit_Framework_IncompleteTest) {
            $this->notImplemented[] = new PHPUnit_Framework_TestFailure($test, $t);
            $notifyMethod           = 'addIncompleteTest';

            if ($this->stopOnIncomplete) {
                $this->stop();
            }
        } elseif ($t instanceof PHPUnit_Framework_SkippedTest) {
            $this->skipped[] = new PHPUnit_Framework_TestFailure($test, $t);
            $notifyMethod    = 'addSkippedTest';

            if ($this->stopOnSkipped) {
                $this->stop();
            }
        } else {
            $this->errors[] = new PHPUnit_Framework_TestFailure($test, $t);
            $notifyMethod   = 'addError';

            if ($this->stopOnError || $this->stopOnFailure) {
                $this->stop();
            }
        }

        // @see https://github.com/sebastianbergmann/phpunit/issues/1953
        if ($t instanceof Error) {
            $t = new PHPUnit_Framework_ExceptionWrapper($t);
        }

        foreach ($this->listeners as $listener) {
            $listener->$notifyMethod($test, $t, $time);
        }

        $this->lastTestFailed = true;
        $this->time          += $time;
    }

    
    public function addWarning(PHPUnit_Framework_Test $test, PHPUnit_Framework_Warning $e, $time)
    {
        if ($this->stopOnWarning) {
            $this->stop();
        }

        $this->warnings[] = new PHPUnit_Framework_TestFailure($test, $e);

        foreach ($this->listeners as $listener) {
            // @todo Remove check for PHPUnit 6.0.0
            // @see  https://github.com/sebastianbergmann/phpunit/pull/1840#issuecomment-162535997
            if (method_exists($listener, 'addWarning')) {
                $listener->addWarning($test, $e, $time);
            }
        }

        $this->time += $time;
    }

    
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        if ($e instanceof PHPUnit_Framework_RiskyTest ||
            $e instanceof PHPUnit_Framework_OutputError) {
            $this->risky[] = new PHPUnit_Framework_TestFailure($test, $e);
            $notifyMethod  = 'addRiskyTest';

            if ($test instanceof PHPUnit_Framework_TestCase) {
                $test->markAsRisky();
            }

            if ($this->stopOnRisky) {
                $this->stop();
            }
        } elseif ($e instanceof PHPUnit_Framework_IncompleteTest) {
            $this->notImplemented[] = new PHPUnit_Framework_TestFailure($test, $e);
            $notifyMethod           = 'addIncompleteTest';

            if ($this->stopOnIncomplete) {
                $this->stop();
            }
        } elseif ($e instanceof PHPUnit_Framework_SkippedTest) {
            $this->skipped[] = new PHPUnit_Framework_TestFailure($test, $e);
            $notifyMethod    = 'addSkippedTest';

            if ($this->stopOnSkipped) {
                $this->stop();
            }
        } else {
            $this->failures[] = new PHPUnit_Framework_TestFailure($test, $e);
            $notifyMethod     = 'addFailure';

            if ($this->stopOnFailure) {
                $this->stop();
            }
        }

        foreach ($this->listeners as $listener) {
            $listener->$notifyMethod($test, $e, $time);
        }

        $this->lastTestFailed = true;
        $this->time          += $time;
    }

    
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if ($this->topTestSuite === null) {
            $this->topTestSuite = $suite;
        }

        foreach ($this->listeners as $listener) {
            $listener->startTestSuite($suite);
        }
    }

    
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        foreach ($this->listeners as $listener) {
            $listener->endTestSuite($suite);
        }
    }

    
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->lastTestFailed = false;
        $this->runTests      += count($test);

        foreach ($this->listeners as $listener) {
            $listener->startTest($test);
        }
    }

    
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        foreach ($this->listeners as $listener) {
            $listener->endTest($test, $time);
        }

        if (!$this->lastTestFailed && $test instanceof PHPUnit_Framework_TestCase) {
            $class  = get_class($test);
            $key    = $class . '::' . $test->getName();

            $this->passed[$key] = [
                'result' => $test->getResult(),
                'size'   => PHPUnit_Util_Test::getSize(
                    $class,
                    $test->getName(false)
                )
            ];

            $this->time += $time;
        }
    }

    
    public function allHarmless()
    {
        return $this->riskyCount() == 0;
    }

    
    public function riskyCount()
    {
        return count($this->risky);
    }

    
    public function allCompletelyImplemented()
    {
        return $this->notImplementedCount() == 0;
    }

    
    public function notImplementedCount()
    {
        return count($this->notImplemented);
    }

    
    public function risky()
    {
        return $this->risky;
    }

    
    public function notImplemented()
    {
        return $this->notImplemented;
    }

    
    public function noneSkipped()
    {
        return $this->skippedCount() == 0;
    }

    
    public function skippedCount()
    {
        return count($this->skipped);
    }

    
    public function skipped()
    {
        return $this->skipped;
    }

    
    public function errorCount()
    {
        return count($this->errors);
    }

    
    public function errors()
    {
        return $this->errors;
    }

    
    public function failureCount()
    {
        return count($this->failures);
    }

    
    public function failures()
    {
        return $this->failures;
    }

    
    public function warningCount()
    {
        return count($this->warnings);
    }

    
    public function warnings()
    {
        return $this->warnings;
    }

    
    public function passed()
    {
        return $this->passed;
    }

    
    public function topTestSuite()
    {
        return $this->topTestSuite;
    }

    
    public function getCollectCodeCoverageInformation()
    {
        return $this->codeCoverage !== null;
    }

    
    public function run(PHPUnit_Framework_Test $test)
    {
        PHPUnit_Framework_Assert::resetCount();

        if ($test instanceof PHPUnit_Framework_TestCase) {
            $test->setRegisterMockObjectsFromTestArgumentsRecursively(
                $this->registerMockObjectsFromTestArgumentsRecursively
            );
        }

        $error      = false;
        $failure    = false;
        $warning    = false;
        $incomplete = false;
        $risky      = false;
        $skipped    = false;

        $this->startTest($test);

        $errorHandlerSet = false;

        if ($this->convertErrorsToExceptions) {
            $oldErrorHandler = set_error_handler(
                ['PHPUnit_Util_ErrorHandler', 'handleError'],
                E_ALL | E_STRICT
            );

            if ($oldErrorHandler === null) {
                $errorHandlerSet = true;
            } else {
                restore_error_handler();
            }
        }

        $collectCodeCoverage = $this->codeCoverage !== null &&
                               !$test instanceof PHPUnit_Framework_WarningTestCase;

        if ($collectCodeCoverage) {
            $this->codeCoverage->start($test);
        }

        $monitorFunctions = $this->beStrictAboutResourceUsageDuringSmallTests &&
                            !$test instanceof PHPUnit_Framework_WarningTestCase &&
                            $test->getSize() == PHPUnit_Util_Test::SMALL &&
                            function_exists('xdebug_start_function_monitor');

        if ($monitorFunctions) {
            xdebug_start_function_monitor(ResourceOperations::getFunctions());
        }

        PHP_Timer::start();

        try {
            if (!$test instanceof PHPUnit_Framework_WarningTestCase &&
                $test->getSize() != PHPUnit_Util_Test::UNKNOWN &&
                $this->enforceTimeLimit &&
                extension_loaded('pcntl') && class_exists('PHP_Invoker')) {
                switch ($test->getSize()) {
                    case PHPUnit_Util_Test::SMALL:
                        $_timeout = $this->timeoutForSmallTests;
                        break;

                    case PHPUnit_Util_Test::MEDIUM:
                        $_timeout = $this->timeoutForMediumTests;
                        break;

                    case PHPUnit_Util_Test::LARGE:
                        $_timeout = $this->timeoutForLargeTests;
                        break;
                }

                $invoker = new PHP_Invoker;
                $invoker->invoke([$test, 'runBare'], [], $_timeout);
            } else {
                $test->runBare();
            }
        } catch (PHPUnit_Framework_MockObject_Exception $e) {
            $e = new PHPUnit_Framework_Warning(
                $e->getMessage()
            );

            $warning = true;
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            $failure = true;

            if ($e instanceof PHPUnit_Framework_RiskyTestError) {
                $risky = true;
            } elseif ($e instanceof PHPUnit_Framework_IncompleteTestError) {
                $incomplete = true;
            } elseif ($e instanceof PHPUnit_Framework_SkippedTestError) {
                $skipped = true;
            }
        } catch (PHPUnit_Framework_Warning $e) {
            $warning = true;
        } catch (PHPUnit_Framework_Exception $e) {
            $error = true;
        } catch (Throwable $e) {
            // @see https://github.com/sebastianbergmann/phpunit/issues/2394
            if (PHP_MAJOR_VERSION === 7 && $e instanceof \AssertionError) {
                $test->addToAssertionCount(1);

                $failure = true;
                $frame   = $e->getTrace()[0];

                $e = new PHPUnit_Framework_AssertionFailedError(
                    sprintf(
                        '%s in %s:%s',
                        $e->getMessage(),
                        $frame['file'],
                        $frame['line']
                    )
                );
            } else {
                $e     = new PHPUnit_Framework_ExceptionWrapper($e);
                $error = true;
            }
        } catch (Exception $e) {
            $e     = new PHPUnit_Framework_ExceptionWrapper($e);
            $error = true;
        }

        $time = PHP_Timer::stop();
        $test->addToAssertionCount(PHPUnit_Framework_Assert::getCount());

        if ($monitorFunctions) {
            $blacklist = new PHPUnit_Util_Blacklist;
            $functions = xdebug_get_monitored_functions();
            xdebug_stop_function_monitor();

            foreach ($functions as $function) {
                if (!$blacklist->isBlacklisted($function['filename'])) {
                    $this->addFailure(
                        $test,
                        new PHPUnit_Framework_RiskyTestError(
                            sprintf(
                                '%s() used in %s:%s',
                                $function['function'],
                                $function['filename'],
                                $function['lineno']
                            )
                        ),
                        $time
                    );
                }
            }
        }

        if ($this->beStrictAboutTestsThatDoNotTestAnything &&
            $test->getNumAssertions() == 0) {
            $risky = true;
        }

        if ($collectCodeCoverage) {
            $append           = !$risky && !$incomplete && !$skipped;
            $linesToBeCovered = [];
            $linesToBeUsed    = [];

            if ($append && $test instanceof PHPUnit_Framework_TestCase) {
                try {
                    $linesToBeCovered = PHPUnit_Util_Test::getLinesToBeCovered(
                        get_class($test),
                        $test->getName(false)
                    );

                    $linesToBeUsed = PHPUnit_Util_Test::getLinesToBeUsed(
                        get_class($test),
                        $test->getName(false)
                    );
                } catch (PHPUnit_Framework_InvalidCoversTargetException $cce) {
                    $this->addWarning(
                        $test,
                        new PHPUnit_Framework_Warning(
                            $cce->getMessage()
                        ),
                        $time
                    );
                }
            }

            try {
                $this->codeCoverage->stop(
                    $append,
                    $linesToBeCovered,
                    $linesToBeUsed
                );
            } catch (UnintentionallyCoveredCodeException $cce) {
                $this->addFailure(
                    $test,
                    new PHPUnit_Framework_UnintentionallyCoveredCodeError(
                        'This test executed code that is not listed as code to be covered or used:' .
                        PHP_EOL . $cce->getMessage()
                    ),
                    $time
                );
            } catch (CoveredCodeNotExecutedException $cce) {
                $this->addFailure(
                    $test,
                    new PHPUnit_Framework_CoveredCodeNotExecutedException(
                        'This test did not execute all the code that is listed as code to be covered:' .
                        PHP_EOL . $cce->getMessage()
                    ),
                    $time
                );
            } catch (MissingCoversAnnotationException $cce) {
                if ($linesToBeCovered !== false) {
                    $this->addFailure(
                        $test,
                        new PHPUnit_Framework_MissingCoversAnnotationException(
                            'This test does not have a @covers annotation but is expected to have one'
                        ),
                        $time
                    );
                }
            } catch (CodeCoverageException $cce) {
                $error = true;

                if (!isset($e)) {
                    $e = $cce;
                }
            }
        }

        if ($errorHandlerSet === true) {
            restore_error_handler();
        }

        if ($error === true) {
            $this->addError($test, $e, $time);
        } elseif ($failure === true) {
            $this->addFailure($test, $e, $time);
        } elseif ($warning === true) {
            $this->addWarning($test, $e, $time);
        } elseif ($this->beStrictAboutTestsThatDoNotTestAnything &&
                  !$test->doesNotPerformAssertions() &&
                  $test->getNumAssertions() == 0) {
            $this->addFailure(
                $test,
                new PHPUnit_Framework_RiskyTestError(
                    'This test did not perform any assertions'
                ),
                $time
            );
        } elseif ($this->beStrictAboutOutputDuringTests && $test->hasOutput()) {
            $this->addFailure(
                $test,
                new PHPUnit_Framework_OutputError(
                    sprintf(
                        'This test printed output: %s',
                        $test->getActualOutput()
                    )
                ),
                $time
            );
        } elseif ($this->beStrictAboutTodoAnnotatedTests && $test instanceof PHPUnit_Framework_TestCase) {
            $annotations = $test->getAnnotations();

            if (isset($annotations['method']['todo'])) {
                $this->addFailure(
                    $test,
                    new PHPUnit_Framework_RiskyTestError(
                        'Test method is annotated with @todo'
                    ),
                    $time
                );
            }
        }

        $this->endTest($test, $time);
    }

    
    public function count()
    {
        return $this->runTests;
    }

    
    public function shouldStop()
    {
        return $this->stop;
    }

    
    public function stop()
    {
        $this->stop = true;
    }

    
    public function getCodeCoverage()
    {
        return $this->codeCoverage;
    }

    
    public function setCodeCoverage(CodeCoverage $codeCoverage)
    {
        $this->codeCoverage = $codeCoverage;
    }

    
    public function convertErrorsToExceptions($flag)
    {
        if (!is_bool($flag)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->convertErrorsToExceptions = $flag;
    }

    
    public function getConvertErrorsToExceptions()
    {
        return $this->convertErrorsToExceptions;
    }

    
    public function stopOnError($flag)
    {
        if (!is_bool($flag)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->stopOnError = $flag;
    }

    
    public function stopOnFailure($flag)
    {
        if (!is_bool($flag)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->stopOnFailure = $flag;
    }

    
    public function stopOnWarning($flag)
    {
        if (!is_bool($flag)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->stopOnWarning = $flag;
    }

    
    public function beStrictAboutTestsThatDoNotTestAnything($flag)
    {
        if (!is_bool($flag)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->beStrictAboutTestsThatDoNotTestAnything = $flag;
    }

    
    public function isStrictAboutTestsThatDoNotTestAnything()
    {
        return $this->beStrictAboutTestsThatDoNotTestAnything;
    }

    
    public function beStrictAboutOutputDuringTests($flag)
    {
        if (!is_bool($flag)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->beStrictAboutOutputDuringTests = $flag;
    }

    
    public function isStrictAboutOutputDuringTests()
    {
        return $this->beStrictAboutOutputDuringTests;
    }

    
    public function beStrictAboutResourceUsageDuringSmallTests($flag)
    {
        if (!is_bool($flag)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->beStrictAboutResourceUsageDuringSmallTests = $flag;
    }

    
    public function isStrictAboutResourceUsageDuringSmallTests()
    {
        return $this->beStrictAboutResourceUsageDuringSmallTests;
    }

    
    public function enforceTimeLimit($flag)
    {
        if (!is_bool($flag)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->enforceTimeLimit = $flag;
    }

    
    public function enforcesTimeLimit()
    {
        return $this->enforceTimeLimit;
    }

    
    public function beStrictAboutTodoAnnotatedTests($flag)
    {
        if (!is_bool($flag)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->beStrictAboutTodoAnnotatedTests = $flag;
    }

    
    public function isStrictAboutTodoAnnotatedTests()
    {
        return $this->beStrictAboutTodoAnnotatedTests;
    }

    
    public function stopOnRisky($flag)
    {
        if (!is_bool($flag)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->stopOnRisky = $flag;
    }

    
    public function stopOnIncomplete($flag)
    {
        if (!is_bool($flag)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->stopOnIncomplete = $flag;
    }

    
    public function stopOnSkipped($flag)
    {
        if (!is_bool($flag)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->stopOnSkipped = $flag;
    }

    
    public function time()
    {
        return $this->time;
    }

    
    public function wasSuccessful($includeWarnings = true)
    {
        if ($includeWarnings) {
            return empty($this->errors) && empty($this->failures) && empty($this->warnings);
        } else {
            return empty($this->errors) && empty($this->failures);
        }
    }

    
    public function setTimeoutForSmallTests($timeout)
    {
        if (!is_integer($timeout)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'integer');
        }

        $this->timeoutForSmallTests = $timeout;
    }

    
    public function setTimeoutForMediumTests($timeout)
    {
        if (!is_integer($timeout)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'integer');
        }

        $this->timeoutForMediumTests = $timeout;
    }

    
    public function setTimeoutForLargeTests($timeout)
    {
        if (!is_integer($timeout)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'integer');
        }

        $this->timeoutForLargeTests = $timeout;
    }

    
    public function getTimeoutForLargeTests()
    {
        return $this->timeoutForLargeTests;
    }

    
    public function setRegisterMockObjectsFromTestArgumentsRecursively($flag)
    {
        if (!is_bool($flag)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->registerMockObjectsFromTestArgumentsRecursively = $flag;
    }

    
    protected function getHierarchy($className, $asReflectionObjects = false)
    {
        if ($asReflectionObjects) {
            $classes = [new ReflectionClass($className)];
        } else {
            $classes = [$className];
        }

        $done = false;

        while (!$done) {
            if ($asReflectionObjects) {
                $class = new ReflectionClass(
                    $classes[count($classes) - 1]->getName()
                );
            } else {
                $class = new ReflectionClass($classes[count($classes) - 1]);
            }

            $parent = $class->getParentClass();

            if ($parent !== false) {
                if ($asReflectionObjects) {
                    $classes[] = $parent;
                } else {
                    $classes[] = $parent->getName();
                }
            } else {
                $done = true;
            }
        }

        return $classes;
    }
}
