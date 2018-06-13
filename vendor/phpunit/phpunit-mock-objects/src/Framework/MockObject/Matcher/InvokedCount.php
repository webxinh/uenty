<?php
/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_MockObject_Matcher_InvokedCount extends PHPUnit_Framework_MockObject_Matcher_InvokedRecorder
{
    
    protected $expectedCount;

    
    public function __construct($expectedCount)
    {
        $this->expectedCount = $expectedCount;
    }

    
    public function isNever()
    {
        return $this->expectedCount == 0;
    }

    
    public function toString()
    {
        return 'invoked ' . $this->expectedCount . ' time(s)';
    }

    
    public function invoked(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        parent::invoked($invocation);

        $count = $this->getInvocationCount();

        if ($count > $this->expectedCount) {
            $message = $invocation->toString() . ' ';

            switch ($this->expectedCount) {
                case 0: {
                    $message .= 'was not expected to be called.';
                }
                break;

                case 1: {
                    $message .= 'was not expected to be called more than once.';
                }
                break;

                default: {
                    $message .= sprintf(
                        'was not expected to be called more than %d times.',
                        $this->expectedCount
                    );
                    }
            }

            throw new PHPUnit_Framework_ExpectationFailedException($message);
        }
    }

    
    public function verify()
    {
        $count = $this->getInvocationCount();

        if ($count !== $this->expectedCount) {
            throw new PHPUnit_Framework_ExpectationFailedException(
                sprintf(
                    'Method was expected to be called %d times, ' .
                    'actually called %d times.',
                    $this->expectedCount,
                    $count
                )
            );
        }
    }
}
