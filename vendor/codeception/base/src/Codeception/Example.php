<?php
namespace Codeception;

use Traversable;

class Example implements \ArrayAccess, \Countable, \IteratorAggregate
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new \PHPUnit_Framework_AssertionFailedError("Example $offset doesn't exist");
        };
        return $this->data[$offset];
    }

    
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    
    public function count()
    {
        return count($this->data);
    }

    
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }
}
