<?php


namespace aabc\caching;

use aabc\base\InvalidConfigException;


class ApcCache extends Cache
{
    
    public $useApcu = false;


    
    public function init()
    {
        parent::init();
        $extension = $this->useApcu ? 'apcu' : 'apc';
        if (!extension_loaded($extension)) {
            throw new InvalidConfigException("ApcCache requires PHP $extension extension to be loaded.");
        }
    }

    
    public function exists($key)
    {
        $key = $this->buildKey($key);

        return $this->useApcu ? apcu_exists($key) : apc_exists($key);
    }

    
    protected function getValue($key)
    {
        return $this->useApcu ? apcu_fetch($key) : apc_fetch($key);
    }

    
    protected function getValues($keys)
    {
        $values = $this->useApcu ? apcu_fetch($keys) : apc_fetch($keys);
        return is_array($values) ? $values : [];
    }

    
    protected function setValue($key, $value, $duration)
    {
        return $this->useApcu ? apcu_store($key, $value, $duration) : apc_store($key, $value, $duration);
    }

    
    protected function setValues($data, $duration)
    {
        $result = $this->useApcu ? apcu_store($data, null, $duration) : apc_store($data, null, $duration);
        return is_array($result) ? array_keys($result) : [];
    }

    
    protected function addValue($key, $value, $duration)
    {
        return $this->useApcu ? apcu_add($key, $value, $duration) : apc_add($key, $value, $duration);
    }

    
    protected function addValues($data, $duration)
    {
        $result = $this->useApcu ? apcu_add($data, null, $duration) : apc_add($data, null, $duration);
        return is_array($result) ? array_keys($result) : [];
    }

    
    protected function deleteValue($key)
    {
        return $this->useApcu ? apcu_delete($key) : apc_delete($key);
    }

    
    protected function flushValues()
    {
        return $this->useApcu ? apcu_clear_cache() : apc_clear_cache('user');
    }
}
