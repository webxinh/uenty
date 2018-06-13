<?php
namespace Codeception\Util;


class Maybe implements \ArrayAccess, \Iterator, \JsonSerializable
{
    protected $position = 0;
    protected $val = null;
    protected $assocArray = null;

    public function __construct($val = null)
    {
        $this->val = $val;
        if (is_array($this->val)) {
            $this->assocArray = $this->isAssocArray($this->val);
        }
        $this->position = 0;
    }

    private function isAssocArray($arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function __toString()
    {
        if ($this->val === null) {
            return "?";
        }
        if (is_scalar($this->val)) {
            return (string)$this->val;
        }

        if (is_object($this->val) && method_exists($this->val, '__toString')) {
            return $this->val->__toString();
        }

        return $this->val;
    }

    public function __get($key)
    {
        if ($this->val === null) {
            return new Maybe();
        }

        if (is_object($this->val)) {
            if (isset($this->val->{$key}) || property_exists($this->val, $key)) {
                return $this->val->{$key};
            }
        }

        return $this->val->key;
    }

    public function __set($key, $val)
    {
        if ($this->val === null) {
            return;
        }

        if (is_object($this->val)) {
            $this->val->{$key} = $val;
            return;
        }

        $this->val->key = $val;
    }

    public function __call($method, $args)
    {
        if ($this->val === null) {
            return new Maybe();
        }
        return call_user_func_array([$this->val, $method], $args);
    }

    public function __clone()
    {
        if (is_object($this->val)) {
            $this->val = clone $this->val;
        }
    }

    public function __unset($key)
    {
        if (is_object($this->val)) {
            if (isset($this->val->{$key}) || property_exists($this->val, $key)) {
                unset($this->val->{$key});
                return;
            }
        }
    }

    public function offsetExists($offset)
    {
        if (is_array($this->val) || ($this->val instanceof \ArrayAccess)) {
            return isset($this->val[$offset]);
        }
        return false;
    }

    public function offsetGet($offset)
    {
        if (is_array($this->val) || ($this->val instanceof \ArrayAccess)) {
            return $this->val[$offset];
        }
        return new Maybe();
    }

    public function offsetSet($offset, $value)
    {
        if (is_array($this->val) || ($this->val instanceof \ArrayAccess)) {
            $this->val[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        if (is_array($this->val) || ($this->val instanceof \ArrayAccess)) {
            unset($this->val[$offset]);
        }
    }

    public function __value()
    {
        $val = $this->val;
        if (is_array($val)) {
            foreach ($val as $k => $v) {
                if ($v instanceof self) {
                    $v = $v->__value();
                }
                $val[$k] = $v;
            }
        }
        return $val;
    }

    
    public function current()
    {
        if (!is_array($this->val)) {
            return null;
        }
        if ($this->assocArray) {
            $keys = array_keys($this->val);
            return $this->val[$keys[$this->position]];
        } else {
            return $this->val[$this->position];
        }
    }

    
    public function next()
    {
        ++$this->position;
    }

    
    public function key()
    {
        if ($this->assocArray) {
            $keys = array_keys($this->val);
            return $keys[$this->position];
        } else {
            return $this->position;
        }
    }

    
    public function valid()
    {
        if (!is_array($this->val)) {
            return null;
        }
        if ($this->assocArray) {
            $keys = array_keys($this->val);
            return isset($keys[$this->position]);
        } else {
            return isset($this->val[$this->position]);
        }
    }

    
    public function rewind()
    {
        if (is_array($this->val)) {
            $this->assocArray = $this->isAssocArray($this->val);
        }
        $this->position = 0;
    }

    
    public function jsonSerialize()
    {
        return $this->__value();
    }
}
