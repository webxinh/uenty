<?php
/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_MockObject_MockBuilder
{
    
    private $testCase;

    
    private $type;

    
    private $methods = [];

    
    private $methodsExcept = [];

    
    private $mockClassName = '';

    
    private $constructorArgs = [];

    
    private $originalConstructor = true;

    
    private $originalClone = true;

    
    private $autoload = true;

    
    private $cloneArguments = false;

    
    private $callOriginalMethods = false;

    
    private $proxyTarget = null;

    
    private $allowMockingUnknownTypes = true;

    
    private $generator;

    
    public function __construct(PHPUnit_Framework_TestCase $testCase, $type)
    {
        $this->testCase  = $testCase;
        $this->type      = $type;
        $this->generator = new PHPUnit_Framework_MockObject_Generator;
    }

    
    public function getMock()
    {
        $object = $this->generator->getMock(
            $this->type,
            $this->methods,
            $this->constructorArgs,
            $this->mockClassName,
            $this->originalConstructor,
            $this->originalClone,
            $this->autoload,
            $this->cloneArguments,
            $this->callOriginalMethods,
            $this->proxyTarget,
            $this->allowMockingUnknownTypes
        );

        $this->testCase->registerMockObject($object);

        return $object;
    }

    
    public function getMockForAbstractClass()
    {
        $object = $this->generator->getMockForAbstractClass(
            $this->type,
            $this->constructorArgs,
            $this->mockClassName,
            $this->originalConstructor,
            $this->originalClone,
            $this->autoload,
            $this->methods,
            $this->cloneArguments
        );

        $this->testCase->registerMockObject($object);

        return $object;
    }

    
    public function getMockForTrait()
    {
        $object = $this->generator->getMockForTrait(
            $this->type,
            $this->constructorArgs,
            $this->mockClassName,
            $this->originalConstructor,
            $this->originalClone,
            $this->autoload,
            $this->methods,
            $this->cloneArguments
        );

        $this->testCase->registerMockObject($object);

        return $object;
    }

    
    public function setMethods(array $methods = null)
    {
        $this->methods = $methods;

        return $this;
    }

    
    public function setMethodsExcept(array $methods = [])
    {
        $this->methodsExcept = $methods;

        $this->setMethods(
            array_diff(
                $this->generator->getClassMethods($this->type),
                $this->methodsExcept
            )
        );

        return $this;
    }

    
    public function setConstructorArgs(array $args)
    {
        $this->constructorArgs = $args;

        return $this;
    }

    
    public function setMockClassName($name)
    {
        $this->mockClassName = $name;

        return $this;
    }

    
    public function disableOriginalConstructor()
    {
        $this->originalConstructor = false;

        return $this;
    }

    
    public function enableOriginalConstructor()
    {
        $this->originalConstructor = true;

        return $this;
    }

    
    public function disableOriginalClone()
    {
        $this->originalClone = false;

        return $this;
    }

    
    public function enableOriginalClone()
    {
        $this->originalClone = true;

        return $this;
    }

    
    public function disableAutoload()
    {
        $this->autoload = false;

        return $this;
    }

    
    public function enableAutoload()
    {
        $this->autoload = true;

        return $this;
    }

    
    public function disableArgumentCloning()
    {
        $this->cloneArguments = false;

        return $this;
    }

    
    public function enableArgumentCloning()
    {
        $this->cloneArguments = true;

        return $this;
    }

    
    public function enableProxyingToOriginalMethods()
    {
        $this->callOriginalMethods = true;

        return $this;
    }

    
    public function disableProxyingToOriginalMethods()
    {
        $this->callOriginalMethods = false;
        $this->proxyTarget         = null;

        return $this;
    }

    
    public function setProxyTarget($object)
    {
        $this->proxyTarget = $object;

        return $this;
    }

    
    public function allowMockingUnknownTypes()
    {
        $this->allowMockingUnknownTypes = true;

        return $this;
    }

    
    public function disallowMockingUnknownTypes()
    {
        $this->allowMockingUnknownTypes = false;

        return $this;
    }
}
