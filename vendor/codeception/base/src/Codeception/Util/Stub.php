<?php

namespace Codeception\Util;

class Stub
{
    public static $magicMethods = ['__isset', '__get', '__set'];

    
    public static function make($class, $params = [], $testCase = false)
    {
        $class = self::getClassname($class);
        if (!class_exists($class)) {
            if (interface_exists($class)) {
                throw new \RuntimeException("Stub::make can't mock interfaces, please use Stub::makeEmpty instead.");
            }
            throw new \RuntimeException("Stubbed class $class doesn't exist.");
        }

        $reflection = new \ReflectionClass($class);
        $callables = self::getMethodsToReplace($reflection, $params);
        if ($reflection->isAbstract()) {
            $arguments = empty($callables) ? [] : array_keys($callables);
            $mock = self::generateMockForAbstractClass($class, $arguments, '', false, $testCase);
        } else {
            $arguments = empty($callables) ? null : array_keys($callables);
            $mock = self::generateMock($class, $arguments, [], '', false, $testCase);
        }

        self::bindParameters($mock, $params);

        return self::markAsMock($mock, $reflection);
    }

    
    private static function markAsMock($mock, \ReflectionClass $reflection)
    {
        if (!$reflection->hasMethod('__set')) {
            $mock->__mocked = $reflection->getName();
        }
        return $mock;
    }

    
    public static function factory($class, $num = 1, $params = [])
    {
        $objects = [];
        for ($i = 0; $i < $num; $i++) {
            $objects[] = self::make($class, $params);
        }

        return $objects;
    }

    
    public static function makeEmptyExcept($class, $method, $params = [], $testCase = false)
    {
        $class = self::getClassname($class);
        $reflectionClass = new \ReflectionClass($class);

        $methods = $reflectionClass->getMethods();

        $methods = array_filter(
            $methods,
            function ($m) {
                return !in_array($m->name, Stub::$magicMethods);
            }
        );

        $methods = array_filter(
            $methods,
            function ($m) use ($method) {
                return $method != $m->name;
            }
        );

        $methods = array_map(
            function ($m) {
                return $m->name;
            },
            $methods
        );

        $methods = count($methods) ? $methods : null;
        $mock = self::generateMock($class, $methods, [], '', false, $testCase);
        self::bindParameters($mock, $params);

        return self::markAsMock($mock, $reflectionClass);
    }

    
    public static function makeEmpty($class, $params = [], $testCase = false)
    {
        $class = self::getClassname($class);
        $reflection = new \ReflectionClass($class);

        $methods = get_class_methods($class);
        $methods = array_filter(
            $methods,
            function ($i) {
                return !in_array($i, Stub::$magicMethods);
            }
        );
        $mock = self::generateMock($class, $methods, [], '', false, $testCase);
        self::bindParameters($mock, $params);

        return self::markAsMock($mock, $reflection);
    }

    
    public static function copy($obj, $params = [])
    {
        $copy = clone($obj);
        self::bindParameters($copy, $params);

        return $copy;
    }

    
    public static function construct($class, $constructorParams = [], $params = [], $testCase = false)
    {
        $class = self::getClassname($class);
        $reflection = new \ReflectionClass($class);

        $callables = self::getMethodsToReplace($reflection, $params);

        $arguments = empty($callables) ? null : array_keys($callables);
        $mock = self::generateMock($class, $arguments, $constructorParams, $testCase);
        self::bindParameters($mock, $params);

        return self::markAsMock($mock, $reflection);
    }

    
    public static function constructEmpty($class, $constructorParams = [], $params = [], $testCase = false)
    {
        $class = self::getClassname($class);
        $reflection = new \ReflectionClass($class);

        $methods = get_class_methods($class);
        $methods = array_filter(
            $methods,
            function ($i) {
                return !in_array($i, Stub::$magicMethods);
            }
        );
        $mock = self::generateMock($class, $methods, $constructorParams, $testCase);
        self::bindParameters($mock, $params);

        return self::markAsMock($mock, $reflection);
    }

    
    public static function constructEmptyExcept(
        $class,
        $method,
        $constructorParams = [],
        $params = [],
        $testCase = false
    ) {
        $class = self::getClassname($class);
        $reflectionClass = new \ReflectionClass($class);
        $methods = $reflectionClass->getMethods();
        $methods = array_filter(
            $methods,
            function ($m) {
                return !in_array($m->name, Stub::$magicMethods);
            }
        );
        $methods = array_filter(
            $methods,
            function ($m) use ($method) {
                return $method != $m->name;
            }
        );
        $methods = array_map(
            function ($m) {
                return $m->name;
            },
            $methods
        );
        $methods = count($methods) ? $methods : null;
        $mock = self::generateMock($class, $methods, $constructorParams, $testCase);
        self::bindParameters($mock, $params);

        return self::markAsMock($mock, $reflectionClass);
    }

    private static function generateMock()
    {
        return self::doGenerateMock(func_get_args());
    }

    
    private static function generateMockForAbstractClass()
    {
        return self::doGenerateMock(func_get_args(), true);
    }

    private static function doGenerateMock($args, $isAbstract = false)
    {
        $testCase = self::extractTestCaseFromArgs($args);
        $methodName = $isAbstract ? 'getMockForAbstractClass' : 'getMock';
        $generatorClass = new \PHPUnit_Framework_MockObject_Generator;

        // using PHPUnit 5.4 mocks registration
        if (version_compare(\PHPUnit_Runner_Version::series(), '5.4', '>=')
            && $testCase instanceof \PHPUnit_Framework_TestCase
        ) {
            $mock = call_user_func_array([$generatorClass, $methodName], $args);
            $testCase->registerMockObject($mock);
            return $mock;
        }

        if ($testCase instanceof  \PHPUnit_Framework_TestCase) {
            $generatorClass = $testCase;
        }

        return call_user_func_array([$generatorClass, $methodName], $args);
    }

    private static function extractTestCaseFromArgs(&$args)
    {
        $argsLength = count($args) - 1;
        $testCase = $args[$argsLength];

        unset($args[$argsLength]);

        return $testCase;
    }

    
    public static function update($mock, array $params)
    {
        //do not rely on __mocked property, check typ eof $mock
        if (!$mock instanceof \PHPUnit_Framework_MockObject_MockObject) {
            throw new \LogicException('You can update only stubbed objects');
        }

        self::bindParameters($mock, $params);

        return $mock;
    }

    
    protected static function bindParameters($mock, $params)
    {
        $reflectionClass = new \ReflectionClass($mock);
        if ($mock instanceof \PHPUnit_Framework_MockObject_MockObject) {
            $parentClass = $reflectionClass->getParentClass();
            if ($parentClass !== false) {
                $reflectionClass = $reflectionClass->getParentClass();
            }
        }

        foreach ($params as $param => $value) {
            // redefine method
            if ($reflectionClass->hasMethod($param)) {
                if ($value instanceof StubMarshaler) {
                    $marshaler = $value;
                    $mock
                        ->expects($marshaler->getMatcher())
                        ->method($param)
                        ->will(new \PHPUnit_Framework_MockObject_Stub_ReturnCallback($marshaler->getValue()));
                } elseif ($value instanceof \Closure) {
                    $mock
                        ->expects(new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)
                        ->method($param)
                        ->will(new \PHPUnit_Framework_MockObject_Stub_ReturnCallback($value));
                } elseif ($value instanceof ConsecutiveMap) {
                    $consecutiveMap = $value;
                    $mock
                        ->expects(new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)
                        ->method($param)
                        ->will(new \PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls($consecutiveMap->getMap()));
                } else {
                    $mock
                        ->expects(new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount)
                        ->method($param)
                        ->will(new \PHPUnit_Framework_MockObject_Stub_Return($value));
                }
            } elseif ($reflectionClass->hasProperty($param)) {
                $reflectionProperty = $reflectionClass->getProperty($param);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($mock, $value);
                continue;
            } else {
                if ($reflectionClass->hasMethod('__set')) {
                    try {
                        $mock->{$param} = $value;
                    } catch (\Exception $e) {
                        throw new \PHPUnit_Framework_Exception(
                            sprintf(
                                'Could not add property %1$s, class %2$s implements __set method, '
                                . 'and no %1$s property exists',
                                $param,
                                $reflectionClass->getName()
                            ),
                            $e->getCode(),
                            $e
                        );
                    }
                } else {
                    $mock->{$param} = $value;
                }
                continue;
            }
        }
    }

    
    protected static function getClassname($object)
    {
        if (is_object($object)) {
            return get_class($object);
        }

        if (is_callable($object)) {
            return call_user_func($object);
        }

        return $object;
    }

    
    protected static function getMethodsToReplace(\ReflectionClass $reflection, $params)
    {
        $callables = [];
        foreach ($params as $method => $value) {
            if ($reflection->hasMethod($method)) {
                $callables[$method] = $value;
            }
        }

        return $callables;
    }

    
    public static function never($params = null)
    {
        return new StubMarshaler(
            new \PHPUnit_Framework_MockObject_Matcher_InvokedCount(0),
            self::closureIfNull($params)
        );
    }

    
    public static function once($params = null)
    {
        return new StubMarshaler(
            new \PHPUnit_Framework_MockObject_Matcher_InvokedCount(1),
            self::closureIfNull($params)
        );
    }

    
    public static function atLeastOnce($params = null)
    {
        return new StubMarshaler(
            new \PHPUnit_Framework_MockObject_Matcher_InvokedAtLeastOnce,
            self::closureIfNull($params)
        );
    }

    
    public static function exactly($count, $params = null)
    {
        return new StubMarshaler(
            new \PHPUnit_Framework_MockObject_Matcher_InvokedCount($count),
            self::closureIfNull($params)
        );
    }

    private static function closureIfNull($params)
    {
        if ($params == null) {
            return function () {
            };
        } else {
            return $params;
        }
    }

    
    public static function consecutive()
    {
        return new ConsecutiveMap(func_get_args());
    }
}
