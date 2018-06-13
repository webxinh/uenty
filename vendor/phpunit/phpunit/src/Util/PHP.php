<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SebastianBergmann\Environment\Runtime;


abstract class PHPUnit_Util_PHP
{
    
    protected $runtime;

    
    protected $stderrRedirection = false;

    
    protected $stdin = '';

    
    protected $args = '';

    
    protected $env = [];

    
    protected $timeout = 0;

    
    public function __construct()
    {
        $this->runtime = new Runtime();
    }

    
    public function setUseStderrRedirection($stderrRedirection)
    {
        if (!is_bool($stderrRedirection)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'boolean');
        }

        $this->stderrRedirection = $stderrRedirection;
    }

    
    public function useStderrRedirection()
    {
        return $this->stderrRedirection;
    }

    
    public function setStdin($stdin)
    {
        $this->stdin = (string) $stdin;
    }

    
    public function getStdin()
    {
        return $this->stdin;
    }

    
    public function setArgs($args)
    {
        $this->args = (string) $args;
    }

    
    public function getArgs()
    {
        return $this->args;
    }

    
    public function setEnv(array $env)
    {
        $this->env = $env;
    }

    
    public function getEnv()
    {
        return $this->env;
    }

    
    public function setTimeout($timeout)
    {
        $this->timeout = (int) $timeout;
    }

    
    public function getTimeout()
    {
        return $this->timeout;
    }

    
    public static function factory()
    {
        if (DIRECTORY_SEPARATOR == '\\') {
            return new PHPUnit_Util_PHP_Windows;
        }

        return new PHPUnit_Util_PHP_Default;
    }

    
    public function runTestJob($job, PHPUnit_Framework_Test $test, PHPUnit_Framework_TestResult $result)
    {
        $result->startTest($test);

        $_result = $this->runJob($job);

        $this->processChildResult(
            $test,
            $result,
            $_result['stdout'],
            $_result['stderr']
        );
    }

    
    public function getCommand(array $settings, $file = null)
    {
        $command = $this->runtime->getBinary();
        $command .= $this->settingsToParameters($settings);

        if ('phpdbg' === PHP_SAPI) {
            $command .= ' -qrr ';

            if ($file) {
                $command .= '-e ' . escapeshellarg($file);
            } else {
                $command .= escapeshellarg(__DIR__ . '/PHP/eval-stdin.php');
            }
        } elseif ($file) {
            $command .= ' -f ' . escapeshellarg($file);
        }

        if ($this->args) {
            $command .= ' -- ' . $this->args;
        }

        if (true === $this->stderrRedirection) {
            $command .= ' 2>&1';
        }

        return $command;
    }

    
    abstract public function runJob($job, array $settings = []);

    
    protected function settingsToParameters(array $settings)
    {
        $buffer = '';

        foreach ($settings as $setting) {
            $buffer .= ' -d ' . $setting;
        }

        return $buffer;
    }

    
    private function processChildResult(PHPUnit_Framework_Test $test, PHPUnit_Framework_TestResult $result, $stdout, $stderr)
    {
        $time = 0;

        if (!empty($stderr)) {
            $result->addError(
                $test,
                new PHPUnit_Framework_Exception(trim($stderr)),
                $time
            );
        } else {
            set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
            });
            try {
                if (strpos($stdout, "#!/usr/bin/env php\n") === 0) {
                    $stdout = substr($stdout, 19);
                }

                $childResult = unserialize(str_replace("#!/usr/bin/env php\n", '', $stdout));
                restore_error_handler();
            } catch (ErrorException $e) {
                restore_error_handler();
                $childResult = false;

                $result->addError(
                    $test,
                    new PHPUnit_Framework_Exception(trim($stdout), 0, $e),
                    $time
                );
            }

            if ($childResult !== false) {
                if (!empty($childResult['output'])) {
                    $output = $childResult['output'];
                }

                $test->setResult($childResult['testResult']);
                $test->addToAssertionCount($childResult['numAssertions']);

                $childResult = $childResult['result'];
                /* @var $childResult PHPUnit_Framework_TestResult */

                if ($result->getCollectCodeCoverageInformation()) {
                    $result->getCodeCoverage()->merge(
                        $childResult->getCodeCoverage()
                    );
                }

                $time           = $childResult->time();
                $notImplemented = $childResult->notImplemented();
                $risky          = $childResult->risky();
                $skipped        = $childResult->skipped();
                $errors         = $childResult->errors();
                $warnings       = $childResult->warnings();
                $failures       = $childResult->failures();

                if (!empty($notImplemented)) {
                    $result->addError(
                        $test,
                        $this->getException($notImplemented[0]),
                        $time
                    );
                } elseif (!empty($risky)) {
                    $result->addError(
                        $test,
                        $this->getException($risky[0]),
                        $time
                    );
                } elseif (!empty($skipped)) {
                    $result->addError(
                        $test,
                        $this->getException($skipped[0]),
                        $time
                    );
                } elseif (!empty($errors)) {
                    $result->addError(
                        $test,
                        $this->getException($errors[0]),
                        $time
                    );
                } elseif (!empty($warnings)) {
                    $result->addWarning(
                        $test,
                        $this->getException($warnings[0]),
                        $time
                    );
                } elseif (!empty($failures)) {
                    $result->addFailure(
                        $test,
                        $this->getException($failures[0]),
                        $time
                    );
                }
            }
        }

        $result->endTest($test, $time);

        if (!empty($output)) {
            print $output;
        }
    }

    
    private function getException(PHPUnit_Framework_TestFailure $error)
    {
        $exception = $error->thrownException();

        if ($exception instanceof __PHP_Incomplete_Class) {
            $exceptionArray = [];
            foreach ((array) $exception as $key => $value) {
                $key                  = substr($key, strrpos($key, "\0") + 1);
                $exceptionArray[$key] = $value;
            }

            $exception = new PHPUnit_Framework_SyntheticError(
                sprintf(
                    '%s: %s',
                    $exceptionArray['_PHP_Incomplete_Class_Name'],
                    $exceptionArray['message']
                ),
                $exceptionArray['code'],
                $exceptionArray['file'],
                $exceptionArray['line'],
                $exceptionArray['trace']
            );
        }

        return $exception;
    }
}
