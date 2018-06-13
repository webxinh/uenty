<?php


namespace aabc\caching;


class XCache extends Cache
{
    
    public function exists($key)
    {
        $key = $this->buildKey($key);

        return xcache_isset($key);
    }

    
    protected function getValue($key)
    {
        return xcache_isset($key) ? xcache_get($key) : false;
    }

    
    protected function setValue($key, $value, $duration)
    {
        return xcache_set($key, $value, $duration);
    }

    
    protected function addValue($key, $value, $duration)
    {
        return !xcache_isset($key) ? $this->setValue($key, $value, $duration) : false;
    }

    
    protected function deleteValue($key)
    {
        return xcache_unset($key);
    }

    
    protected function flushValues()
    {
        for ($i = 0, $max = xcache_count(XC_TYPE_VAR); $i < $max; $i++) {
            if (xcache_clear_cache(XC_TYPE_VAR, $i) === false) {
                return false;
            }
        }

        return true;
    }
}
