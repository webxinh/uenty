<?php


namespace aabc\web;

use Aabc;
use ArrayIterator;
use aabc\base\InvalidCallException;
use aabc\base\Object;


class CookieCollection extends Object implements \IteratorAggregate, \ArrayAccess, \Countable
{
    
    public $readOnly = false;

    
    private $_cookies = [];


    
    public function __construct($cookies = [], $config = [])
    {
        $this->_cookies = $cookies;
        parent::__construct($config);
    }

    
    public function getIterator()
    {
        return new ArrayIterator($this->_cookies);
    }

    
    public function count()
    {
        return $this->getCount();
    }

    
    public function getCount()
    {
        return count($this->_cookies);
    }

    
    public function get($name)
    {
        return isset($this->_cookies[$name]) ? $this->_cookies[$name] : null;
    }

    
    public function getValue($name, $defaultValue = null)
    {
        return isset($this->_cookies[$name]) ? $this->_cookies[$name]->value : $defaultValue;
    }

    
    public function has($name)
    {
        return isset($this->_cookies[$name]) && $this->_cookies[$name]->value !== ''
            && ($this->_cookies[$name]->expire === null || $this->_cookies[$name]->expire >= time());
    }

    
    public function add($cookie)
    {
        if ($this->readOnly) {
            throw new InvalidCallException('The cookie collection is read only.');
        }
        $this->_cookies[$cookie->name] = $cookie;
    }

    
    public function remove($cookie, $removeFromBrowser = true)
    {
        if ($this->readOnly) {
            throw new InvalidCallException('The cookie collection is read only.');
        }
        if ($cookie instanceof Cookie) {
            $cookie->expire = 1;
            $cookie->value = '';
        } else {
            $cookie = new Cookie([
                'name' => $cookie,
                'expire' => 1,
            ]);
        }
        if ($removeFromBrowser) {
            $this->_cookies[$cookie->name] = $cookie;
        } else {
            unset($this->_cookies[$cookie->name]);
        }
    }

    
    public function removeAll()
    {
        if ($this->readOnly) {
            throw new InvalidCallException('The cookie collection is read only.');
        }
        $this->_cookies = [];
    }

    
    public function toArray()
    {
        return $this->_cookies;
    }

    
    public function fromArray(array $array)
    {
        $this->_cookies = $array;
    }

    
    public function offsetExists($name)
    {
        return $this->has($name);
    }

    
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    
    public function offsetSet($name, $cookie)
    {
        $this->add($cookie);
    }

    
    public function offsetUnset($name)
    {
        $this->remove($name);
    }
}
