<?php
namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Codeception\TestInterface;
use Codeception\Exception\ModuleConfigException;


class Memcache extends CodeceptionModule
{
    
    public $memcache = null;

    
    protected $config = [
        'host' => 'localhost',
        'port' => 11211
    ];

    
    public function _before(TestInterface $test)
    {
        if (class_exists('\Memcache')) {
            $this->memcache = new \Memcache;
            $this->memcache->connect($this->config['host'], $this->config['port']);
        } elseif (class_exists('\Memcached')) {
            $this->memcache = new \Memcached;
            $this->memcache->addServer($this->config['host'], $this->config['port']);
        } else {
            throw new ModuleConfigException(__CLASS__, 'Memcache classes not loaded');
        }
    }

    
    public function _after(TestInterface $test)
    {
        $this->memcache->flush();
        switch (get_class($this->memcache)) {
            case 'Memcache':
                $this->memcache->close();
                break;
            case 'Memcached':
                $this->memcache->quit();
                break;
        }
    }

    
    public function grabValueFromMemcached($key)
    {
        $value = $this->memcache->get($key);
        $this->debugSection("Value", $value);

        return $value;
    }

    
    public function seeInMemcached($key, $value = null)
    {
        $actual = $this->memcache->get($key);
        $this->debugSection("Value", $actual);

        if (null === $value) {
            $this->assertTrue(false !== $actual, "Cannot find key '$key' in Memcached");
        } else {
            $this->assertEquals($value, $actual, "Cannot find key '$key' in Memcached with the provided value");
        }
    }

    
    public function dontSeeInMemcached($key, $value = null)
    {
        $actual = $this->memcache->get($key);
        $this->debugSection("Value", $actual);

        if (null === $value) {
            $this->assertTrue(false === $actual, "The key '$key' exists in Memcached");
        } else {
            if (false !== $actual) {
                $this->assertEquals($value, $actual, "The key '$key' exists in Memcached with the provided value");
            }
        }
    }

    
    public function haveInMemcached($key, $value, $expiration = null)
    {
        switch (get_class($this->memcache)) {
            case 'Memcache':
                $this->assertTrue($this->memcache->set($key, $value, null, $expiration));
                break;
            case 'Memcached':
                $this->assertTrue($this->memcache->set($key, $value, $expiration));
                break;
        }
    }

    
    public function clearMemcache()
    {
        $this->memcache->flush();
    }
}
