<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_SkippedTestCase extends PHPUnit_Framework_TestCase
{
    
    protected $message = '';

    
    protected $backupGlobals = false;

    
    protected $backupStaticAttributes = false;

    
    protected $runTestInSeparateProcess = false;

    
    protected $useErrorHandler = false;

    
    protected $useOutputBuffering = false;

    
    public function __construct($className, $methodName, $message = '')
    {
        $this->message = $message;
        parent::__construct($className . '::' . $methodName);
    }

    
    protected function runTest()
    {
        $this->markTestSkipped($this->message);
    }

    
    public function getMessage()
    {
        return $this->message;
    }

    
    public function toString()
    {
        return $this->getName();
    }
}
