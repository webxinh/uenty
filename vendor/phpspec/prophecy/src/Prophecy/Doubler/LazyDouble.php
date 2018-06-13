<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prophecy\Doubler;

use Prophecy\Exception\Doubler\DoubleException;
use Prophecy\Exception\Doubler\ClassNotFoundException;
use Prophecy\Exception\Doubler\InterfaceNotFoundException;
use ReflectionClass;


class LazyDouble
{
    private $doubler;
    private $class;
    private $interfaces = array();
    private $arguments  = null;
    private $double;

    
    public function __construct(Doubler $doubler)
    {
        $this->doubler = $doubler;
    }

    
    public function setParentClass($class)
    {
        if (null !== $this->double) {
            throw new DoubleException('Can not extend class with already instantiated double.');
        }

        if (!$class instanceof ReflectionClass) {
            if (!class_exists($class)) {
                throw new ClassNotFoundException(sprintf('Class %s not found.', $class), $class);
            }

            $class = new ReflectionClass($class);
        }

        $this->class = $class;
    }

    
    public function addInterface($interface)
    {
        if (null !== $this->double) {
            throw new DoubleException(
                'Can not implement interface with already instantiated double.'
            );
        }

        if (!$interface instanceof ReflectionClass) {
            if (!interface_exists($interface)) {
                throw new InterfaceNotFoundException(
                    sprintf('Interface %s not found.', $interface),
                    $interface
                );
            }

            $interface = new ReflectionClass($interface);
        }

        $this->interfaces[] = $interface;
    }

    
    public function setArguments(array $arguments = null)
    {
        $this->arguments = $arguments;
    }

    
    public function getInstance()
    {
        if (null === $this->double) {
            if (null !== $this->arguments) {
                return $this->double = $this->doubler->double(
                    $this->class, $this->interfaces, $this->arguments
                );
            }

            $this->double = $this->doubler->double($this->class, $this->interfaces);
        }

        return $this->double;
    }
}
