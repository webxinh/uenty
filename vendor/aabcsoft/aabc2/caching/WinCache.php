<?php


namespace aabc\caching;


class WinCache extends Cache
{
    
    public function exists($key)
    {
        $key = $this->buildKey($key);

        return wincache_ucache_exists($key);
    }

    
    protected function getValue($key)
    {
        return wincache_ucache_get($key);
    }

    
    protected function getValues($keys)
    {
        return wincache_ucache_get($keys);
    }

    
    protected function setValue($key, $value, $duration)
    {
        return wincache_ucache_set($key, $value, $duration);
    }

    
    protected function setValues($data, $duration)
    {
        return wincache_ucache_set($data, null, $duration);
    }

    
    protected function addValue($key, $value, $duration)
    {
        return wincache_ucache_add($key, $value, $duration);
    }

    
    protected function addValues($data, $duration)
    {
        return wincache_ucache_add($data, null, $duration);
    }

    
    protected function deleteValue($key)
    {
        return wincache_ucache_delete($key);
    }

    
    protected function flushValues()
    {
        return wincache_ucache_clear();
    }
}
