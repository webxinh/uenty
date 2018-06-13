<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Extensions_TestDecorator extends PHPUnit_Framework_Assert implements PHPUnit_Framework_Test, PHPUnit_Framework_SelfDescribing
{
    
    protected $test = null;

    
    public function __construct(PHPUnit_Framework_Test $test)
    {
        $this->test = $test;
    }

    
    public function toString()
    {
        return $this->test->toString();
    }

    
    public function basicRun(PHPUnit_Framework_TestResult $result)
    {
        $this->test->run($result);
    }

    
    public function count()
    {
        return count($this->test);
    }

    
    protected function createResult()
    {
        return new PHPUnit_Framework_TestResult;
    }

    
    public function getTest()
    {
        return $this->test;
    }

    
    public function run(PHPUnit_Framework_TestResult $result = null)
    {
        if ($result === null) {
            $result = $this->createResult();
        }

        $this->basicRun($result);

        return $result;
    }
}
