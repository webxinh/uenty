<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_WarningTestCase extends PHPUnit_Framework_TestCase
{
    
    protected $message = '';

    
    protected $backupGlobals = false;

    
    protected $backupStaticAttributes = false;

    
    protected $runTestInSeparateProcess = false;

    
    protected $useErrorHandler = false;

    
    public function __construct($message = '')
    {
        $this->message = $message;
        parent::__construct('Warning');
    }

    
    protected function runTest()
    {
        throw new PHPUnit_Framework_Warning($this->message);
    }

    
    public function getMessage()
    {
        return $this->message;
    }

    
    public function toString()
    {
        return 'Warning';
    }
}
