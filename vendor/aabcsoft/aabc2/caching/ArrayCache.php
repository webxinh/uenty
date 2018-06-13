<?php


namespace aabc\caching;


class ArrayCache extends Cache
{
    private $_cache;


    
    public function exists($key)
    {
        $key = $this->buildKey($key);
        return isset($this->_cache[$key]) && ($this->_cache[$key][1] === 0 || $this->_cache[$key][1] > microtime(true));
    }

    
    protected function getValue($key)
    {
        if (isset($this->_cache[$key]) && ($this->_cache[$key][1] === 0 || $this->_cache[$key][1] > microtime(true))) {
            return $this->_cache[$key][0];
        }
        return false;
    }

    
    protected function setValue($key, $value, $duration)
    {
        $this->_cache[$key] = [$value, $duration === 0 ? 0 : microtime(true) + $duration];
        return true;
    }

    
    protected function addValue($key, $value, $duration)
    {
        if (isset($this->_cache[$key]) && ($this->_cache[$key][1] === 0 || $this->_cache[$key][1] > microtime(true))) {
            return false;
        }
        $this->_cache[$key] = [$value, $duration === 0 ? 0 : microtime(true) + $duration];
        return true;
    }

    
    protected function deleteValue($key)
    {
        unset($this->_cache[$key]);
        return true;
    }

    
    protected function flushValues()
    {
        $this->_cache = [];
        return true;
    }
}