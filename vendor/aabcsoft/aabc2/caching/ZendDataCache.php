<?php


namespace aabc\caching;


class ZendDataCache extends Cache
{
    
    protected function getValue($key)
    {
        $result = zend_shm_cache_fetch($key);

        return $result === null ? false : $result;
    }

    
    protected function setValue($key, $value, $duration)
    {
        return zend_shm_cache_store($key, $value, $duration);
    }

    
    protected function addValue($key, $value, $duration)
    {
        return zend_shm_cache_fetch($key) === null ? $this->setValue($key, $value, $duration) : false;
    }

    
    protected function deleteValue($key)
    {
        return zend_shm_cache_delete($key);
    }

    
    protected function flushValues()
    {
        return zend_shm_cache_clear();
    }
}
