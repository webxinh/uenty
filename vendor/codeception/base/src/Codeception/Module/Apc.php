<?php
namespace Codeception\Module;

use Codeception\Module;
use Codeception\TestInterface;
use Codeception\Exception\ModuleException;


class Apc extends Module
{
    
    public function _before(TestInterface $test)
    {
        if (!extension_loaded('apc') && !extension_loaded('apcu')) {
            throw new ModuleException(
                __CLASS__,
                'The APC(u) extension not loaded.'
            );
        }

        if (!ini_get('apc.enabled') || (PHP_SAPI === 'cli' && !ini_get('apc.enable_cli'))) {
            throw new ModuleException(
                __CLASS__,
                'The "apc.enable_cli" parameter must be set to "On".'
            );
        }
    }

    
    public function _after(TestInterface $test)
    {
        $this->clear();
    }

    
    public function grabValueFromApc($key)
    {
        $value = $this->fetch($key);
        $this->debugSection('Value', $value);

        return $value;
    }

    
    public function seeInApc($key, $value = null)
    {
        if (null === $value) {
            $this->assertTrue($this->exists($key), "Cannot find key '$key' in APC(u).");
            return;
        }

        $actual = $this->grabValueFromApc($key);
        $this->assertEquals($value, $actual, "Cannot find key '$key' in APC(u) with the provided value.");
    }

    
    public function dontSeeInApc($key, $value = null)
    {
        if (null === $value) {
            $this->assertFalse($this->exists($key), "The key '$key' exists in APC(u).");
            return;
        }

        $actual = $this->grabValueFromApc($key);
        if (false !== $actual) {
            $this->assertEquals($value, $actual, "The key '$key' exists in APC(u) with the provided value.");
        }
    }

    
    public function haveInApc($key, $value, $expiration = null)
    {
        $this->store($key, $value, $expiration);

        return $key;
    }

    
    public function flushApc()
    {
        // Returns TRUE always
        $this->clear();
    }

    
    protected function clear()
    {
        if (function_exists('apcu_clear_cache')) {
            return apcu_clear_cache();
        }

        return apc_clear_cache('user');
    }

    
    protected function exists($key)
    {
        if (function_exists('apcu_exists')) {
            return apcu_exists($key);
        }

        return apc_exists($key);
    }

    
    protected function fetch($key)
    {
        $success = false;

        if (function_exists('apcu_fetch')) {
            $data = apcu_fetch($key, $success);
        } else {
            $data = apc_fetch($key, $success);
        }

        $this->debugSection('Fetching a stored variable', $success ? 'OK' : 'FAILED');

        return $data;
    }

    
    protected function store($key, $var, $ttl = 0)
    {
        if (function_exists('apcu_store')) {
            return apcu_store($key, $var, $ttl);
        }

        return apc_store($key, $var, $ttl);
    }
}
