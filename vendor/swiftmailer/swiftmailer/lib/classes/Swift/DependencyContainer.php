<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_DependencyContainer
{
    
    const TYPE_VALUE = 0x0001;

    
    const TYPE_INSTANCE = 0x0010;

    
    const TYPE_SHARED = 0x0100;

    
    const TYPE_ALIAS = 0x1000;

    
    private static $_instance = null;

    
    private $_store = array();

    
    private $_endPoint;

    
    public function __construct()
    {
    }

    
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    
    public function listItems()
    {
        return array_keys($this->_store);
    }

    
    public function has($itemName)
    {
        return array_key_exists($itemName, $this->_store)
            && isset($this->_store[$itemName]['lookupType']);
    }

    
    public function lookup($itemName)
    {
        if (!$this->has($itemName)) {
            throw new Swift_DependencyException(
                'Cannot lookup dependency "'.$itemName.'" since it is not registered.'
                );
        }

        switch ($this->_store[$itemName]['lookupType']) {
            case self::TYPE_ALIAS:
                return $this->_createAlias($itemName);
            case self::TYPE_VALUE:
                return $this->_getValue($itemName);
            case self::TYPE_INSTANCE:
                return $this->_createNewInstance($itemName);
            case self::TYPE_SHARED:
                return $this->_createSharedInstance($itemName);
        }
    }

    
    public function createDependenciesFor($itemName)
    {
        $args = array();
        if (isset($this->_store[$itemName]['args'])) {
            $args = $this->_resolveArgs($this->_store[$itemName]['args']);
        }

        return $args;
    }

    
    public function register($itemName)
    {
        $this->_store[$itemName] = array();
        $this->_endPoint = &$this->_store[$itemName];

        return $this;
    }

    
    public function asValue($value)
    {
        $endPoint = &$this->_getEndPoint();
        $endPoint['lookupType'] = self::TYPE_VALUE;
        $endPoint['value'] = $value;

        return $this;
    }

    
    public function asAliasOf($lookup)
    {
        $endPoint = &$this->_getEndPoint();
        $endPoint['lookupType'] = self::TYPE_ALIAS;
        $endPoint['ref'] = $lookup;

        return $this;
    }

    
    public function asNewInstanceOf($className)
    {
        $endPoint = &$this->_getEndPoint();
        $endPoint['lookupType'] = self::TYPE_INSTANCE;
        $endPoint['className'] = $className;

        return $this;
    }

    
    public function asSharedInstanceOf($className)
    {
        $endPoint = &$this->_getEndPoint();
        $endPoint['lookupType'] = self::TYPE_SHARED;
        $endPoint['className'] = $className;

        return $this;
    }

    
    public function withDependencies(array $lookups)
    {
        $endPoint = &$this->_getEndPoint();
        $endPoint['args'] = array();
        foreach ($lookups as $lookup) {
            $this->addConstructorLookup($lookup);
        }

        return $this;
    }

    
    public function addConstructorValue($value)
    {
        $endPoint = &$this->_getEndPoint();
        if (!isset($endPoint['args'])) {
            $endPoint['args'] = array();
        }
        $endPoint['args'][] = array('type' => 'value', 'item' => $value);

        return $this;
    }

    
    public function addConstructorLookup($lookup)
    {
        $endPoint = &$this->_getEndPoint();
        if (!isset($this->_endPoint['args'])) {
            $endPoint['args'] = array();
        }
        $endPoint['args'][] = array('type' => 'lookup', 'item' => $lookup);

        return $this;
    }

    
    private function _getValue($itemName)
    {
        return $this->_store[$itemName]['value'];
    }

    
    private function _createAlias($itemName)
    {
        return $this->lookup($this->_store[$itemName]['ref']);
    }

    
    private function _createNewInstance($itemName)
    {
        $reflector = new ReflectionClass($this->_store[$itemName]['className']);
        if ($reflector->getConstructor()) {
            return $reflector->newInstanceArgs(
                $this->createDependenciesFor($itemName)
                );
        }

        return $reflector->newInstance();
    }

    
    private function _createSharedInstance($itemName)
    {
        if (!isset($this->_store[$itemName]['instance'])) {
            $this->_store[$itemName]['instance'] = $this->_createNewInstance($itemName);
        }

        return $this->_store[$itemName]['instance'];
    }

    
    private function &_getEndPoint()
    {
        if (!isset($this->_endPoint)) {
            throw new BadMethodCallException(
                'Component must first be registered by calling register()'
                );
        }

        return $this->_endPoint;
    }

    
    private function _resolveArgs(array $args)
    {
        $resolved = array();
        foreach ($args as $argDefinition) {
            switch ($argDefinition['type']) {
                case 'lookup':
                    $resolved[] = $this->_lookupRecursive($argDefinition['item']);
                    break;
                case 'value':
                    $resolved[] = $argDefinition['item'];
                    break;
            }
        }

        return $resolved;
    }

    
    private function _lookupRecursive($item)
    {
        if (is_array($item)) {
            $collection = array();
            foreach ($item as $k => $v) {
                $collection[$k] = $this->_lookupRecursive($v);
            }

            return $collection;
        }

        return $this->lookup($item);
    }
}
