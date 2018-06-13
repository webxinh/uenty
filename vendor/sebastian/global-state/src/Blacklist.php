<?php
/*
 * This file is part of the GlobalState package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\GlobalState;

use ReflectionClass;


class Blacklist
{
    
    private $globalVariables = array();

    
    private $classes = array();

    
    private $classNamePrefixes = array();

    
    private $parentClasses = array();

    
    private $interfaces = array();

    
    private $staticAttributes = array();

    
    public function addGlobalVariable($variableName)
    {
        $this->globalVariables[$variableName] = true;
    }

    
    public function addClass($className)
    {
        $this->classes[] = $className;
    }

    
    public function addSubclassesOf($className)
    {
        $this->parentClasses[] = $className;
    }

    
    public function addImplementorsOf($interfaceName)
    {
        $this->interfaces[] = $interfaceName;
    }

    
    public function addClassNamePrefix($classNamePrefix)
    {
        $this->classNamePrefixes[] = $classNamePrefix;
    }

    
    public function addStaticAttribute($className, $attributeName)
    {
        if (!isset($this->staticAttributes[$className])) {
            $this->staticAttributes[$className] = array();
        }

        $this->staticAttributes[$className][$attributeName] = true;
    }

    
    public function isGlobalVariableBlacklisted($variableName)
    {
        return isset($this->globalVariables[$variableName]);
    }

    
    public function isStaticAttributeBlacklisted($className, $attributeName)
    {
        if (in_array($className, $this->classes)) {
            return true;
        }

        foreach ($this->classNamePrefixes as $prefix) {
            if (strpos($className, $prefix) === 0) {
                return true;
            }
        }

        $class = new ReflectionClass($className);

        foreach ($this->parentClasses as $type) {
            if ($class->isSubclassOf($type)) {
                return true;
            }
        }

        foreach ($this->interfaces as $type) {
            if ($class->implementsInterface($type)) {
                return true;
            }
        }

        if (isset($this->staticAttributes[$className][$attributeName])) {
            return true;
        }

        return false;
    }
}
