<?php
/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_MockObject_Matcher_InvokedAtIndex implements PHPUnit_Framework_MockObject_Matcher_Invocation
{
    
    protected $sequenceIndex;

    
    protected $currentIndex = -1;

    
    public function __construct($sequenceIndex)
    {
        $this->sequenceIndex = $sequenceIndex;
    }

    
    public function toString()
    {
        return 'invoked at sequence index ' . $this->sequenceIndex;
    }

    
    public function matches(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        $this->currentIndex++;

        return $this->currentIndex == $this->sequenceIndex;
    }

    
    public function invoked(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
    }

    
    public function verify()
    {
        if ($this->currentIndex < $this->sequenceIndex) {
            throw new PHPUnit_Framework_ExpectationFailedException(
                sprintf(
                    'The expected invocation at index %s was never reached.',
                    $this->sequenceIndex
                )
            );
        }
    }
}
