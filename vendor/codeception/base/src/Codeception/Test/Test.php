<?php
namespace Codeception\Test;

use Codeception\TestInterface;


abstract class Test implements TestInterface, Interfaces\Descriptive
{
    use Feature\AssertionCounter;
    use Feature\CodeCoverage;
    use Feature\ErrorLogger;
    use Feature\MetadataCollector;
    use Feature\IgnoreIfMetadataBlocked;

    private $testResult;
    private $ignored = false;

    
    protected $hooks = [
      'ignoreIfMetadataBlocked',
      'codeCoverage',
      'assertionCounter',
      'errorLogger'
    ];

    const STATUS_FAIL = 'fail';
    const STATUS_ERROR = 'error';
    const STATUS_OK = 'ok';
    const STATUS_PENDING = 'pending';

    
    abstract public function test();

    
    abstract public function toString();

    
    final public function run(\PHPUnit_Framework_TestResult $result = null)
    {
        $this->testResult = $result;

        $status = self::STATUS_PENDING;
        $time = 0;
        $e = null;
        
        try {
            $result->startTest($this);
        } catch (\Exception $er) {
            // failure is created: not a user's test code error so we don't need detailed stacktrace
            $this->testResult->addError($this, new \PHPUnit_Framework_AssertionFailedError($er->getMessage()), 0);
            $this->ignored = true;
        }

        foreach ($this->hooks as $hook) {
            if (method_exists($this, $hook.'Start')) {
                $this->{$hook.'Start'}();
            }
        }
        
        if (!$this->ignored) {
            \PHP_Timer::start();
            try {
                $this->test();
                $status = self::STATUS_OK;
            } catch (\PHPUnit_Framework_AssertionFailedError $e) {
                $status = self::STATUS_FAIL;
            } catch (\PHPUnit_Framework_Exception $e) {
                $status = self::STATUS_ERROR;
            } catch (\Throwable $e) {
                $e     = new \PHPUnit_Framework_ExceptionWrapper($e);
                $status = self::STATUS_ERROR;
            } catch (\Exception $e) {
                $e     = new \PHPUnit_Framework_ExceptionWrapper($e);
                $status = self::STATUS_ERROR;
            }
            $time = \PHP_Timer::stop();
        }

        foreach (array_reverse($this->hooks) as $hook) {
            if (method_exists($this, $hook.'End')) {
                $this->{$hook.'End'}($status, $time, $e);
            }
        }

        $result->endTest($this, $time);
        return $result;
    }

    public function getTestResultObject()
    {
        return $this->testResult;
    }

    
    public function count()
    {
        return 1;
    }

    
    protected function ignore($ignored)
    {
        $this->ignored = $ignored;
    }
}
