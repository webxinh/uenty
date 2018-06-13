<?php
/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


abstract class PHPUnit_Framework_MockObject_Matcher_InvokedRecorder implements PHPUnit_Framework_MockObject_Matcher_Invocation
{
    
    protected $invocations = [];

    
    public function getInvocationCount()
    {
        return count($this->invocations);
    }

    
    public function getInvocations()
    {
        return $this->invocations;
    }

    
    public function hasBeenInvoked()
    {
        return count($this->invocations) > 0;
    }

    
    public function invoked(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        $this->invocations[] = $invocation;
    }

    
    public function matches(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        return true;
    }
}
