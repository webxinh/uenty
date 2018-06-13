<?php
/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_MockObject_Matcher_ConsecutiveParameters extends PHPUnit_Framework_MockObject_Matcher_StatelessInvocation
{
    
    private $parameterGroups = [];

    
    private $invocations = [];

    
    public function __construct(array $parameterGroups)
    {
        foreach ($parameterGroups as $index => $parameters) {
            foreach ($parameters as $parameter) {
                if (!$parameter instanceof PHPUnit_Framework_Constraint) {
                    $parameter = new PHPUnit_Framework_Constraint_IsEqual($parameter);
                }

                $this->parameterGroups[$index][] = $parameter;
            }
        }
    }

    
    public function toString()
    {
        $text = 'with consecutive parameters';

        return $text;
    }

    
    public function matches(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        $this->invocations[] = $invocation;
        $callIndex           = count($this->invocations) - 1;

        $this->verifyInvocation($invocation, $callIndex);

        return false;
    }

    public function verify()
    {
        foreach ($this->invocations as $callIndex => $invocation) {
            $this->verifyInvocation($invocation, $callIndex);
        }
    }

    
    private function verifyInvocation(PHPUnit_Framework_MockObject_Invocation $invocation, $callIndex)
    {
        if (isset($this->parameterGroups[$callIndex])) {
            $parameters = $this->parameterGroups[$callIndex];
        } else {
            // no parameter assertion for this call index
            return;
        }

        if ($invocation === null) {
            throw new PHPUnit_Framework_ExpectationFailedException(
                'Mocked method does not exist.'
            );
        }

        if (count($invocation->parameters) < count($parameters)) {
            throw new PHPUnit_Framework_ExpectationFailedException(
                sprintf(
                    'Parameter count for invocation %s is too low.',
                    $invocation->toString()
                )
            );
        }

        foreach ($parameters as $i => $parameter) {
            $parameter->evaluate(
                $invocation->parameters[$i],
                sprintf(
                    'Parameter %s for invocation #%d %s does not match expected ' .
                    'value.',
                    $i,
                    $callIndex,
                    $invocation->toString()
                )
            );
        }
    }
}
