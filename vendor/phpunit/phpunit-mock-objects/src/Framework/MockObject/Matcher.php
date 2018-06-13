<?php
/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_MockObject_Matcher implements PHPUnit_Framework_MockObject_Matcher_Invocation
{
    
    public $invocationMatcher;

    
    public $afterMatchBuilderId = null;

    
    public $afterMatchBuilderIsInvoked = false;

    
    public $methodNameMatcher = null;

    
    public $parametersMatcher = null;

    
    public $stub = null;

    
    public function __construct(PHPUnit_Framework_MockObject_Matcher_Invocation $invocationMatcher)
    {
        $this->invocationMatcher = $invocationMatcher;
    }

    
    public function toString()
    {
        $list = [];

        if ($this->invocationMatcher !== null) {
            $list[] = $this->invocationMatcher->toString();
        }

        if ($this->methodNameMatcher !== null) {
            $list[] = 'where ' . $this->methodNameMatcher->toString();
        }

        if ($this->parametersMatcher !== null) {
            $list[] = 'and ' . $this->parametersMatcher->toString();
        }

        if ($this->afterMatchBuilderId !== null) {
            $list[] = 'after ' . $this->afterMatchBuilderId;
        }

        if ($this->stub !== null) {
            $list[] = 'will ' . $this->stub->toString();
        }

        return implode(' ', $list);
    }

    
    public function invoked(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        if ($this->invocationMatcher === null) {
            throw new PHPUnit_Framework_MockObject_RuntimeException(
                'No invocation matcher is set'
            );
        }

        if ($this->methodNameMatcher === null) {
            throw new PHPUnit_Framework_MockObject_RuntimeException('No method matcher is set');
        }

        if ($this->afterMatchBuilderId !== null) {
            $builder = $invocation->object
                                  ->__phpunit_getInvocationMocker()
                                  ->lookupId($this->afterMatchBuilderId);

            if (!$builder) {
                throw new PHPUnit_Framework_MockObject_RuntimeException(
                    sprintf(
                        'No builder found for match builder identification <%s>',
                        $this->afterMatchBuilderId
                    )
                );
            }

            $matcher = $builder->getMatcher();

            if ($matcher && $matcher->invocationMatcher->hasBeenInvoked()) {
                $this->afterMatchBuilderIsInvoked = true;
            }
        }

        $this->invocationMatcher->invoked($invocation);

        try {
            if ($this->parametersMatcher !== null &&
                !$this->parametersMatcher->matches($invocation)) {
                $this->parametersMatcher->verify();
            }
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            throw new PHPUnit_Framework_ExpectationFailedException(
                sprintf(
                    "Expectation failed for %s when %s\n%s",
                    $this->methodNameMatcher->toString(),
                    $this->invocationMatcher->toString(),
                    $e->getMessage()
                ),
                $e->getComparisonFailure()
            );
        }

        if ($this->stub) {
            return $this->stub->invoke($invocation);
        }

        return $invocation->generateReturnValue();
    }

    
    public function matches(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        if ($this->afterMatchBuilderId !== null) {
            $builder = $invocation->object
                                  ->__phpunit_getInvocationMocker()
                                  ->lookupId($this->afterMatchBuilderId);

            if (!$builder) {
                throw new PHPUnit_Framework_MockObject_RuntimeException(
                    sprintf(
                        'No builder found for match builder identification <%s>',
                        $this->afterMatchBuilderId
                    )
                );
            }

            $matcher = $builder->getMatcher();

            if (!$matcher) {
                return false;
            }

            if (!$matcher->invocationMatcher->hasBeenInvoked()) {
                return false;
            }
        }

        if ($this->invocationMatcher === null) {
            throw new PHPUnit_Framework_MockObject_RuntimeException(
                'No invocation matcher is set'
            );
        }

        if ($this->methodNameMatcher === null) {
            throw new PHPUnit_Framework_MockObject_RuntimeException('No method matcher is set');
        }

        if (!$this->invocationMatcher->matches($invocation)) {
            return false;
        }

        try {
            if (!$this->methodNameMatcher->matches($invocation)) {
                return false;
            }
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            throw new PHPUnit_Framework_ExpectationFailedException(
                sprintf(
                    "Expectation failed for %s when %s\n%s",
                    $this->methodNameMatcher->toString(),
                    $this->invocationMatcher->toString(),
                    $e->getMessage()
                ),
                $e->getComparisonFailure()
            );
        }

        return true;
    }

    
    public function verify()
    {
        if ($this->invocationMatcher === null) {
            throw new PHPUnit_Framework_MockObject_RuntimeException(
                'No invocation matcher is set'
            );
        }

        if ($this->methodNameMatcher === null) {
            throw new PHPUnit_Framework_MockObject_RuntimeException('No method matcher is set');
        }

        try {
            $this->invocationMatcher->verify();

            if ($this->parametersMatcher === null) {
                $this->parametersMatcher = new PHPUnit_Framework_MockObject_Matcher_AnyParameters;
            }

            $invocationIsAny   = $this->invocationMatcher instanceof PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount;
            $invocationIsNever = $this->invocationMatcher instanceof PHPUnit_Framework_MockObject_Matcher_InvokedCount && $this->invocationMatcher->isNever();

            if (!$invocationIsAny && !$invocationIsNever) {
                $this->parametersMatcher->verify();
            }
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            throw new PHPUnit_Framework_ExpectationFailedException(
                sprintf(
                    "Expectation failed for %s when %s.\n%s",
                    $this->methodNameMatcher->toString(),
                    $this->invocationMatcher->toString(),
                    PHPUnit_Framework_TestFailure::exceptionToString($e)
                )
            );
        }
    }

    
    public function hasMatchers()
    {
        if ($this->invocationMatcher !== null &&
            !$this->invocationMatcher instanceof PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount) {
            return true;
        }

        return false;
    }
}
