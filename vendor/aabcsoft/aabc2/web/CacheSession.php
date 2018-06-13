<?php


namespace aabc\web;

use Aabc;
use aabc\caching\Cache;
use aabc\di\Instance;


class CacheSession extends Session
{
    
    public $cache = 'cache';


    
    public function init()
    {
        parent::init();
        $this->cache = Instance::ensure($this->cache, Cache::className());
    }

    
    public function getUseCustomStorage()
    {
        return true;
    }

    
    public function readSession($id)
    {
        $data = $this->cache->get($this->calculateKey($id));

        return $data === false ? '' : $data;
    }

    
    public function writeSession($id, $data)
    {
        return $this->cache->set($this->calculateKey($id), $data, $this->getTimeout());
    }

    
    public function destroySession($id)
    {
        return $this->cache->delete($this->calculateKey($id));
    }

    
    protected function calculateKey($id)
    {
        return [__CLASS__, $id];
    }
}
