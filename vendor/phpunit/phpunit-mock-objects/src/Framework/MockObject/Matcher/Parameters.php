<?php
/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_MockObject_Matcher_Parameters extends PHPUnit_Framework_MockObject_Matcher_StatelessInvocation
{
    
    protected $parameters = [];

    
    protected $invocation;

    
    private $parameterVerificationResult;

    
    public function __construct(array $parameters)
    {
        foreach ($parameters as $parameter) {
            if (!($parameter instanceof PHPUnit_Framework_Constraint)) {
                $parameter = new PHPUnit_Framework_Constraint_IsEqual(
                    $parameter
                );
            }

            $this->parameters[] = $parameter;
        }
    }

    
    public function toString()
    {
        $text = 'with parameter';

        foreach ($this->parameters as $index => $parameter) {
            if ($index > 0) {
                $text .= ' and';
            }

            $text .= ' ' . $index . ' ' . $parameter->toString();
        }

        return $text;
    }

    
    public function matches(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        $this->invocation                  = $invocation;
        $this->parameterVerificationResult = null;

        try {
            $this->parameterVerificationResult = $this->verify();

            return $this->parameterVerificationResult;
        } catch (PHPUnit_Framework_ExpectationFailedException $e) {
            $this->parameterVerificationResult = $e;

            throw $this->parameterVerificationResult;
        }
    }

    
    public function verify()
    {
        if (isset($this->parameterVerificationResult)) {
            return $this->guardAgainstDuplicateEvaluationOfParameterConstraints();
        }

        if ($this->invocation === null) {
            throw new PHPUnit_Framework_ExpectationFailedException(
                'Mocked method does not exist.'
            );
        }

        if (count($this->invocation->parameters) < count($this->parameters)) {
            $message = 'Parameter count for invocation %s is too low.';

            // The user called `->with($this->anything())`, but may have meant
            // `->withAnyParameters()`.
            //
            // @see https://github.com/sebastianbergmann/phpunit-mock-objects/issues/199
            if (count($this->parameters) === 1 &&
                get_class($this->parameters[0]) === 'PHPUnit_Framework_Constraint_IsAnything') {
                $message .= "\nTo allow 0 or more parameters with any value, omit ->with() or use ->withAnyParameters() instead.";
            }

            throw new PHPUnit_Framework_ExpectationFailedException(
                sprintf($message, $this->invocation->toString())
            );
        }

        foreach ($this->parameters as $i => $parameter) {
            $parameter->evaluate(
                $this->invocation->parameters[$i],
                sprintf(
                    'Parameter %s for invocation %s does not match expected ' .
                    'value.',
                    $i,
                    $this->invocation->toString()
                )
            );
        }

        return true;
    }

    
    private function guardAgainstDuplicateEvaluationOfParameterConstraints()
    {
        if ($this->parameterVerificationResult instanceof Exception) {
            throw $this->parameterVerificationResult;
        }

        return (bool) $this->parameterVerificationResult;
    }
}
