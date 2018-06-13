<?php
/*
 * This file is part of the php-code-coverage package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\CodeCoverage\Node;

use SebastianBergmann\CodeCoverage\Util;


abstract class AbstractNode implements \Countable
{
    
    private $name;

    
    private $path;

    
    private $pathArray;

    
    private $parent;

    
    private $id;

    
    public function __construct($name, AbstractNode $parent = null)
    {
        if (substr($name, -1) == '/') {
            $name = substr($name, 0, -1);
        }

        $this->name   = $name;
        $this->parent = $parent;
    }

    
    public function getName()
    {
        return $this->name;
    }

    
    public function getId()
    {
        if ($this->id === null) {
            $parent = $this->getParent();

            if ($parent === null) {
                $this->id = 'index';
            } else {
                $parentId = $parent->getId();

                if ($parentId == 'index') {
                    $this->id = str_replace(':', '_', $this->name);
                } else {
                    $this->id = $parentId . '/' . $this->name;
                }
            }
        }

        return $this->id;
    }

    
    public function getPath()
    {
        if ($this->path === null) {
            if ($this->parent === null || $this->parent->getPath() === null || $this->parent->getPath() === false) {
                $this->path = $this->name;
            } else {
                $this->path = $this->parent->getPath() . '/' . $this->name;
            }
        }

        return $this->path;
    }

    
    public function getPathAsArray()
    {
        if ($this->pathArray === null) {
            if ($this->parent === null) {
                $this->pathArray = [];
            } else {
                $this->pathArray = $this->parent->getPathAsArray();
            }

            $this->pathArray[] = $this;
        }

        return $this->pathArray;
    }

    
    public function getParent()
    {
        return $this->parent;
    }

    
    public function getTestedClassesPercent($asString = true)
    {
        return Util::percent(
            $this->getNumTestedClasses(),
            $this->getNumClasses(),
            $asString
        );
    }

    
    public function getTestedTraitsPercent($asString = true)
    {
        return Util::percent(
            $this->getNumTestedTraits(),
            $this->getNumTraits(),
            $asString
        );
    }

    
    public function getTestedClassesAndTraitsPercent($asString = true)
    {
        return Util::percent(
            $this->getNumTestedClassesAndTraits(),
            $this->getNumClassesAndTraits(),
            $asString
        );
    }

    
    public function getTestedMethodsPercent($asString = true)
    {
        return Util::percent(
            $this->getNumTestedMethods(),
            $this->getNumMethods(),
            $asString
        );
    }

    
    public function getLineExecutedPercent($asString = true)
    {
        return Util::percent(
            $this->getNumExecutedLines(),
            $this->getNumExecutableLines(),
            $asString
        );
    }

    
    public function getNumClassesAndTraits()
    {
        return $this->getNumClasses() + $this->getNumTraits();
    }

    
    public function getNumTestedClassesAndTraits()
    {
        return $this->getNumTestedClasses() + $this->getNumTestedTraits();
    }

    
    public function getClassesAndTraits()
    {
        return array_merge($this->getClasses(), $this->getTraits());
    }

    
    abstract public function getClasses();

    
    abstract public function getTraits();

    
    abstract public function getFunctions();

    
    abstract public function getLinesOfCode();

    
    abstract public function getNumExecutableLines();

    
    abstract public function getNumExecutedLines();

    
    abstract public function getNumClasses();

    
    abstract public function getNumTestedClasses();

    
    abstract public function getNumTraits();

    
    abstract public function getNumTestedTraits();

    
    abstract public function getNumMethods();

    
    abstract public function getNumTestedMethods();

    
    abstract public function getNumFunctions();

    
    abstract public function getNumTestedFunctions();
}
