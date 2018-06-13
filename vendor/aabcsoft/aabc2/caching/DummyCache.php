<?php


namespace aabc\caching;


class DummyCache extends Cache
{
    
    protected function getValue($key)
    {
        return false;
    }

    
    protected function setValue($key, $value, $duration)
    {
        return true;
    }

    
    protected function addValue($key, $value, $duration)
    {
        return true;
    }

    
    protected function deleteValue($key)
    {
        return true;
    }

    
    protected function flushValues()
    {
        return true;
    }
}
