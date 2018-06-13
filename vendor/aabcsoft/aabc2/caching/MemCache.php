<?php


namespace aabc\caching;

use Aabc;
use aabc\base\InvalidConfigException;


class MemCache extends Cache
{
    
    public $useMemcached = false;
    
    public $persistentId;
    
    public $options;
    
    public $username;
    
    public $password;

    
    private $_cache;
    
    private $_servers = [];


    
    public function init()
    {
        parent::init();
        $this->addServers($this->getMemcache(), $this->getServers());
    }

    
    protected function addServers($cache, $servers)
    {
        if (empty($servers)) {
            $servers = [new MemCacheServer([
                'host' => '127.0.0.1',
                'port' => 11211,
            ])];
        } else {
            foreach ($servers as $server) {
                if ($server->host === null) {
                    throw new InvalidConfigException("The 'host' property must be specified for every memcache server.");
                }
            }
        }
        if ($this->useMemcached) {
            $this->addMemcachedServers($cache, $servers);
        } else {
            $this->addMemcacheServers($cache, $servers);
        }
    }

    
    protected function addMemcachedServers($cache, $servers)
    {
        $existingServers = [];
        if ($this->persistentId !== null) {
            foreach ($cache->getServerList() as $s) {
                $existingServers[$s['host'] . ':' . $s['port']] = true;
            }
        }
        foreach ($servers as $server) {
            if (empty($existingServers) || !isset($existingServers[$server->host . ':' . $server->port])) {
                $cache->addServer($server->host, $server->port, $server->weight);
            }
        }
    }

    
    protected function addMemcacheServers($cache, $servers)
    {
        $class = new \ReflectionClass($cache);
        $paramCount = $class->getMethod('addServer')->getNumberOfParameters();
        foreach ($servers as $server) {
            // $timeout is used for memcache versions that do not have $timeoutms parameter
            $timeout = (int) ($server->timeout / 1000) + (($server->timeout % 1000 > 0) ? 1 : 0);
            if ($paramCount === 9) {
                $cache->addserver(
                    $server->host,
                    $server->port,
                    $server->persistent,
                    $server->weight,
                    $timeout,
                    $server->retryInterval,
                    $server->status,
                    $server->failureCallback,
                    $server->timeout
                );
            } else {
                $cache->addserver(
                    $server->host,
                    $server->port,
                    $server->persistent,
                    $server->weight,
                    $timeout,
                    $server->retryInterval,
                    $server->status,
                    $server->failureCallback
                );
            }
        }
    }

    
    public function getMemcache()
    {
        if ($this->_cache === null) {
            $extension = $this->useMemcached ? 'memcached' : 'memcache';
            if (!extension_loaded($extension)) {
                throw new InvalidConfigException("MemCache requires PHP $extension extension to be loaded.");
            }

            if ($this->useMemcached) {
                $this->_cache = $this->persistentId !== null ? new \Memcached($this->persistentId) : new \Memcached;
                if ($this->username !== null || $this->password !== null) {
                    $this->_cache->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
                    $this->_cache->setSaslAuthData($this->username, $this->password);
                }
                if (!empty($this->options)) {
                    $this->_cache->setOptions($this->options);
                }
            } else {
                $this->_cache = new \Memcache;
            }
        }

        return $this->_cache;
    }

    
    public function getServers()
    {
        return $this->_servers;
    }

    
    public function setServers($config)
    {
        foreach ($config as $c) {
            $this->_servers[] = new MemCacheServer($c);
        }
    }

    
    protected function getValue($key)
    {
        return $this->_cache->get($key);
    }

    
    protected function getValues($keys)
    {
        return $this->useMemcached ? $this->_cache->getMulti($keys) : $this->_cache->get($keys);
    }

    
    protected function setValue($key, $value, $duration)
    {
        // Use UNIX timestamp since it doesn't have any limitation
        // @see http://php.net/manual/en/memcache.set.php
        // @see http://php.net/manual/en/memcached.expiration.php
        $expire = $duration > 0 ? $duration + time() : 0;

        return $this->useMemcached ? $this->_cache->set($key, $value, $expire) : $this->_cache->set($key, $value, 0, $expire);
    }

    
    protected function setValues($data, $duration)
    {
        if ($this->useMemcached) {
            // Use UNIX timestamp since it doesn't have any limitation
            // @see http://php.net/manual/en/memcache.set.php
            // @see http://php.net/manual/en/memcached.expiration.php
            $expire = $duration > 0 ? $duration + time() : 0;
            $this->_cache->setMulti($data, $expire);

            return [];
        } else {
            return parent::setValues($data, $duration);
        }
    }

    
    protected function addValue($key, $value, $duration)
    {
        // Use UNIX timestamp since it doesn't have any limitation
        // @see http://php.net/manual/en/memcache.set.php
        // @see http://php.net/manual/en/memcached.expiration.php
        $expire = $duration > 0 ? $duration + time() : 0;

        return $this->useMemcached ? $this->_cache->add($key, $value, $expire) : $this->_cache->add($key, $value, 0, $expire);
    }

    
    protected function deleteValue($key)
    {
        return $this->_cache->delete($key, 0);
    }

    
    protected function flushValues()
    {
        return $this->_cache->flush();
    }
}
