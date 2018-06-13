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

use Doctrine\Instantiator\Instantiator;
use Prophecy\Doubler\ClassPatch\ClassPatchInterface;
use Prophecy\Doubler\Generator\ClassMirror;
use Prophecy\Doubler\Generator\ClassCreator;
use Prophecy\Exception\InvalidArgumentException;
use ReflectionClass;


class Doubler
{
    private $mirror;
    private $creator;
    private $namer;

    
    private $patches = array();

    
    private $instantiator;

    
    public function __construct(ClassMirror $mirror = null, ClassCreator $creator = null,
                                NameGenerator $namer = null)
    {
        $this->mirror  = $mirror  ?: new ClassMirror;
        $this->creator = $creator ?: new ClassCreator;
        $this->namer   = $namer   ?: new NameGenerator;
    }

    
    public function getClassPatches()
    {
        return $this->patches;
    }

    
    public function registerClassPatch(ClassPatchInterface $patch)
    {
        $this->patches[] = $patch;

        @usort($this->patches, function (ClassPatchInterface $patch1, ClassPatchInterface $patch2) {
            return $patch2->getPriority() - $patch1->getPriority();
        });
    }

    
    public function double(ReflectionClass $class = null, array $interfaces, array $args = null)
    {
        foreach ($interfaces as $interface) {
            if (!$interface instanceof ReflectionClass) {
                throw new InvalidArgumentException(sprintf(
                    "[ReflectionClass \$interface1 [, ReflectionClass \$interface2]] array expected as\n".
                    "a second argument to `Doubler::double(...)`, but got %s.",
                    is_object($interface) ? get_class($interface).' class' : gettype($interface)
                ));
            }
        }

        $classname  = $this->createDoubleClass($class, $interfaces);
        $reflection = new ReflectionClass($classname);

        if (null !== $args) {
            return $reflection->newInstanceArgs($args);
        }
        if ((null === $constructor = $reflection->getConstructor())
            || ($constructor->isPublic() && !$constructor->isFinal())) {
            return $reflection->newInstance();
        }

        if (!$this->instantiator) {
            $this->instantiator = new Instantiator();
        }

        return $this->instantiator->instantiate($classname);
    }

    
    protected function createDoubleClass(ReflectionClass $class = null, array $interfaces)
    {
        $name = $this->namer->name($class, $interfaces);
        $node = $this->mirror->reflect($class, $interfaces);

        foreach ($this->patches as $patch) {
            if ($patch->supports($node)) {
                $patch->apply($node);
            }
        }

        $this->creator->create($name, $node);

        return $name;
    }
}
