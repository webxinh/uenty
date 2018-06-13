<?php


namespace aabc\web;

use Aabc;
use aabc\base\Object;
use ArrayIterator;


class HeaderCollection extends Object implements \IteratorAggregate, \ArrayAccess, \Countable
{
    
    private $_headers = [];


    
    public function getIterator()
    {
        return new ArrayIterator($this->_headers);
    }

    
    public function count()
    {
        return $this->getCount();
    }

    
    public function getCount()
    {
        return count($this->_headers);
    }

    
    public function get($name, $default = null, $first = true)
    {
        $name = strtolower($name);
        if (isset($this->_headers[$name])) {
            return $first ? reset($this->_headers[$name]) : $this->_headers[$name];
        } else {
            return $default;
        }
    }

    
    public function set($name, $value = '')
    {
        $name = strtolower($name);
        $this->_headers[$name] = (array) $value;

        return $this;
    }

    
    public function add($name, $value)
    {
        $name = strtolower($name);
        $this->_headers[$name][] = $value;

        return $this;
    }

    
    public function setDefault($name, $value)
    {
        $name = strtolower($name);
        if (empty($this->_headers[$name])) {
            $this->_headers[$name][] = $value;
        }

        return $this;
    }

    
    public function has($name)
    {
        $name = strtolower($name);

        return isset($this->_headers[$name]);
    }

    
    public function remove($name)
    {
        $name = strtolower($name);
        if (isset($this->_headers[$name])) {
            $value = $this->_headers[$name];
            unset($this->_headers[$name]);
            return $value;
        } else {
            return null;
        }
    }

    
    public function removeAll()
    {
        $this->_headers = [];
    }

    
    public function toArray()
    {
        return $this->_headers;
    }

    
    public function fromArray(array $array)
    {
        $this->_headers = $array;
    }

    
    public function offsetExists($name)
    {
        return $this->has($name);
    }

    
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    
    public function offsetSet($name, $value)
    {
        $this->set($name, $value);
    }

    
    public function offsetUnset($name)
    {
        $this->remove($name);
    }
}
