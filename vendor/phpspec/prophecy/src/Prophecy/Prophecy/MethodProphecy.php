<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prophecy\Prophecy;

use Prophecy\Argument;
use Prophecy\Prophet;
use Prophecy\Promise;
use Prophecy\Prediction;
use Prophecy\Exception\Doubler\MethodNotFoundException;
use Prophecy\Exception\InvalidArgumentException;
use Prophecy\Exception\Prophecy\MethodProphecyException;


class MethodProphecy
{
    private $objectProphecy;
    private $methodName;
    private $argumentsWildcard;
    private $promise;
    private $prediction;
    private $checkedPredictions = array();
    private $bound = false;

    
    public function __construct(ObjectProphecy $objectProphecy, $methodName, $arguments = null)
    {
        $double = $objectProphecy->reveal();
        if (!method_exists($double, $methodName)) {
            throw new MethodNotFoundException(sprintf(
                'Method `%s::%s()` is not defined.', get_class($double), $methodName
            ), get_class($double), $methodName, $arguments);
        }

        $this->objectProphecy = $objectProphecy;
        $this->methodName     = $methodName;

        $reflectedMethod = new \ReflectionMethod($double, $methodName);
        if ($reflectedMethod->isFinal()) {
            throw new MethodProphecyException(sprintf(
                "Can not add prophecy for a method `%s::%s()`\n".
                "as it is a final method.",
                get_class($double),
                $methodName
            ), $this);
        }

        if (null !== $arguments) {
            $this->withArguments($arguments);
        }

        if (version_compare(PHP_VERSION, '7.0', '>=') && true === $reflectedMethod->hasReturnType()) {
            $type = (string) $reflectedMethod->getReturnType();
            $this->will(function () use ($type) {
                switch ($type) {
                    case 'string': return '';
                    case 'float':  return 0.0;
                    case 'int':    return 0;
                    case 'bool':   return false;
                    case 'array':  return array();

                    case 'callable':
                    case 'Closure':
                        return function () {};

                    case 'Traversable':
                    case 'Generator':
                        // Remove eval() when minimum version >=5.5
                        
                        $generator = eval('return function () { yield; };');
                        return $generator();

                    default:
                        $prophet = new Prophet;
                        return $prophet->prophesize($type)->reveal();
                }
            });
        }
    }

    
    public function withArguments($arguments)
    {
        if (is_array($arguments)) {
            $arguments = new Argument\ArgumentsWildcard($arguments);
        }

        if (!$arguments instanceof Argument\ArgumentsWildcard) {
            throw new InvalidArgumentException(sprintf(
                "Either an array or an instance of ArgumentsWildcard expected as\n".
                'a `MethodProphecy::withArguments()` argument, but got %s.',
                gettype($arguments)
            ));
        }

        $this->argumentsWildcard = $arguments;

        return $this;
    }

    
    public function will($promise)
    {
        if (is_callable($promise)) {
            $promise = new Promise\CallbackPromise($promise);
        }

        if (!$promise instanceof Promise\PromiseInterface) {
            throw new InvalidArgumentException(sprintf(
                'Expected callable or instance of PromiseInterface, but got %s.',
                gettype($promise)
            ));
        }

        $this->bindToObjectProphecy();
        $this->promise = $promise;

        return $this;
    }

    
    public function willReturn()
    {
        return $this->will(new Promise\ReturnPromise(func_get_args()));
    }

    
    public function willReturnArgument($index = 0)
    {
        return $this->will(new Promise\ReturnArgumentPromise($index));
    }

    
    public function willThrow($exception)
    {
        return $this->will(new Promise\ThrowPromise($exception));
    }

    
    public function should($prediction)
    {
        if (is_callable($prediction)) {
            $prediction = new Prediction\CallbackPrediction($prediction);
        }

        if (!$prediction instanceof Prediction\PredictionInterface) {
            throw new InvalidArgumentException(sprintf(
                'Expected callable or instance of PredictionInterface, but got %s.',
                gettype($prediction)
            ));
        }

        $this->bindToObjectProphecy();
        $this->prediction = $prediction;

        return $this;
    }

    
    public function shouldBeCalled()
    {
        return $this->should(new Prediction\CallPrediction);
    }

    
    public function shouldNotBeCalled()
    {
        return $this->should(new Prediction\NoCallsPrediction);
    }

    
    public function shouldBeCalledTimes($count)
    {
        return $this->should(new Prediction\CallTimesPrediction($count));
    }

    
    public function shouldHave($prediction)
    {
        if (is_callable($prediction)) {
            $prediction = new Prediction\CallbackPrediction($prediction);
        }

        if (!$prediction instanceof Prediction\PredictionInterface) {
            throw new InvalidArgumentException(sprintf(
                'Expected callable or instance of PredictionInterface, but got %s.',
                gettype($prediction)
            ));
        }

        if (null === $this->promise) {
            $this->willReturn();
        }

        $calls = $this->getObjectProphecy()->findProphecyMethodCalls(
            $this->getMethodName(),
            $this->getArgumentsWildcard()
        );

        try {
            $prediction->check($calls, $this->getObjectProphecy(), $this);
            $this->checkedPredictions[] = $prediction;
        } catch (\Exception $e) {
            $this->checkedPredictions[] = $prediction;

            throw $e;
        }

        return $this;
    }

    
    public function shouldHaveBeenCalled()
    {
        return $this->shouldHave(new Prediction\CallPrediction);
    }

    
    public function shouldNotHaveBeenCalled()
    {
        return $this->shouldHave(new Prediction\NoCallsPrediction);
    }

    
    public function shouldNotBeenCalled()
    {
        return $this->shouldNotHaveBeenCalled();
    }

    
    public function shouldHaveBeenCalledTimes($count)
    {
        return $this->shouldHave(new Prediction\CallTimesPrediction($count));
    }

    
    public function checkPrediction()
    {
        if (null === $this->prediction) {
            return;
        }

        $this->shouldHave($this->prediction);
    }

    
    public function getPromise()
    {
        return $this->promise;
    }

    
    public function getPrediction()
    {
        return $this->prediction;
    }

    
    public function getCheckedPredictions()
    {
        return $this->checkedPredictions;
    }

    
    public function getObjectProphecy()
    {
        return $this->objectProphecy;
    }

    
    public function getMethodName()
    {
        return $this->methodName;
    }

    
    public function getArgumentsWildcard()
    {
        return $this->argumentsWildcard;
    }

    private function bindToObjectProphecy()
    {
        if ($this->bound) {
            return;
        }

        $this->getObjectProphecy()->addMethodProphecy($this);
        $this->bound = true;
    }
}
