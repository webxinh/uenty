<?php
/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastCount extends PHPUnit_Framework_MockObject_Matcher_InvokedRecorder
{
    
    private $requiredInvocations;

    
    public function __construct($requiredInvocations)
    {
        $this->requiredInvocations = $requiredInvocations;
    }

    
    public function toString()
    {
        return 'invoked at least ' . $this->requiredInvocations . ' times';
    }

    
    public function verify()
    {
        $count = $this->getInvocationCount();

        if ($count < $this->requiredInvocations) {
            throw new PHPUnit_Framework_ExpectationFailedException(
                'Expected invocation at least ' . $this->requiredInvocations .
                ' times but it occurred ' . $count . ' time(s).'
            );
        }
    }
}
