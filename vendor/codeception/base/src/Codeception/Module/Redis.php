<?php

namespace Codeception\Module;

use Codeception\Lib\Interfaces\RequiresPackage;
use Codeception\Module as CodeceptionModule;
use Codeception\Exception\ModuleException;
use Codeception\TestInterface;
use Predis\Client as RedisDriver;


class Redis extends CodeceptionModule implements RequiresPackage
{
    
    protected $config = [
        'host'          => '127.0.0.1',
        'port'          => 6379,
        'cleanupBefore' => 'test'
    ];

    
    protected $requiredFields = [
        'database'
    ];

    
    public $driver;

    public function _requires()
    {
        return ['Predis\Client' => '"predis/predis": "^1.0"'];
    }

    
    public function _initialize()
    {
        try {
            $this->driver = new RedisDriver([
                'host'     => $this->config['host'],
                'port'     => $this->config['port'],
                'database' => $this->config['database']
            ]);
        } catch (\Exception $e) {
            throw new ModuleException(
                __CLASS__,
                $e->getMessage()
            );
        }
    }

    
    public function _beforeSuite($settings = [])
    {
        if ($this->config['cleanupBefore'] === 'suite') {
            $this->cleanup();
        }
    }

    
    public function _before(TestInterface $test)
    {
        if ($this->config['cleanupBefore'] === 'test') {
            $this->cleanup();
        }
    }

    
    public function cleanup()
    {
        try {
            $this->driver->flushdb();
        } catch (\Exception $e) {
            throw new ModuleException(
                __CLASS__,
                $e->getMessage()
            );
        }
    }

    
    public function grabFromRedis($key)
    {
        $args = func_get_args();

        switch ($this->driver->type($key)) {
            case 'none':
                throw new ModuleException(
                    $this,
                    "Cannot grab key \"$key\" as it does not exist"
                );
                break;

            case 'string':
                $reply = $this->driver->get($key);
                break;

            case 'list':
                if (count($args) === 2) {
                    $reply = $this->driver->lindex($key, $args[1]);
                } else {
                    $reply = $this->driver->lrange(
                        $key,
                        isset($args[1]) ? $args[1] : 0,
                        isset($args[2]) ? $args[2] : -1
                    );
                }
                break;

            case 'set':
                $reply = $this->driver->smembers($key);
                break;

            case 'zset':
                if (count($args) === 2) {
                    throw new ModuleException(
                        $this,
                        "The method grabFromRedis(), when used with sorted "
                        . "sets, expects either one argument or three"
                    );
                }
                $reply = $this->driver->zrange(
                    $key,
                    isset($args[2]) ? $args[1] : 0,
                    isset($args[2]) ? $args[2] : -1,
                    'WITHSCORES'
                );
                break;

            case 'hash':
                $reply = isset($args[1])
                    ? $this->driver->hget($key, $args[1])
                    : $this->driver->hgetall($key);
                break;

            default:
                $reply = null;
        }

        return $reply;
    }

    
    public function haveInRedis($type, $key, $value)
    {
        switch (strtolower($type)) {
            case 'string':
                if (!is_scalar($value)) {
                    throw new ModuleException(
                        $this,
                        'If second argument of haveInRedis() method is "string", '
                        . 'third argument must be a scalar'
                    );
                }
                $this->driver->set($key, $value);
                break;

            case 'list':
                $this->driver->rpush($key, $value);
                break;

            case 'set':
                $this->driver->sadd($key, $value);
                break;

            case 'zset':
                if (!is_array($value)) {
                    throw new ModuleException(
                        $this,
                        'If second argument of haveInRedis() method is "zset", '
                        . 'third argument must be an (associative) array'
                    );
                }
                $this->driver->zadd($key, $value);
                break;

            case 'hash':
                if (!is_array($value)) {
                    throw new ModuleException(
                        $this,
                        'If second argument of haveInRedis() method is "hash", '
                        . 'third argument must be an array'
                    );
                }
                $this->driver->hmset($key, $value);
                break;

            default:
                throw new ModuleException(
                    $this,
                    "Unknown type \"$type\" for key \"$key\". Allowed types are "
                    . '"string", "list", "set", "zset", "hash"'
                );
        }
    }

    
    public function dontSeeInRedis($key, $value = null)
    {
        $this->assertFalse(
            (bool) $this->checkKeyExists($key, $value),
            "The key \"$key\" exists" . ($value ? ' and its value matches the one provided' : '')
        );
    }

    
    public function dontSeeRedisKeyContains($key, $item, $itemValue = null)
    {
        $this->assertFalse(
            (bool) $this->checkKeyContains($key, $item, $itemValue),
            "The key \"$key\" contains " . (
                is_null($itemValue)
                ? "\"$item\""
                : "[\"$item\" => \"$itemValue\"]"
            )
        );
    }

    
    public function seeInRedis($key, $value = null)
    {
        $this->assertTrue(
            (bool) $this->checkKeyExists($key, $value),
            "Cannot find key \"$key\"" . ($value ? ' with the provided value' : '')
        );
    }

    
    public function sendCommandToRedis($command)
    {
        return call_user_func_array(
            [$this->driver, $command],
            array_slice(func_get_args(), 1)
        );
    }

    
    public function seeRedisKeyContains($key, $item, $itemValue = null)
    {
        $this->assertTrue(
            (bool) $this->checkKeyContains($key, $item, $itemValue),
            "The key \"$key\" does not contain " . (
            is_null($itemValue)
                ? "\"$item\""
                : "[\"$item\" => \"$itemValue\"]"
            )
        );
    }

    
    private function boolToString($var)
    {
        $copy = is_array($var) ? $var : [$var];

        foreach ($copy as $key => $value) {
            if (is_bool($value)) {
                $copy[$key] = $value ? '1' : '0';
            }
        }

        return is_array($var) ? $copy : $copy[0];
    }

    
    private function checkKeyContains($key, $item, $itemValue = null)
    {
        $result = null;

        if (!is_scalar($item)) {
            throw new ModuleException(
                $this,
                "All arguments of [dont]seeRedisKeyContains() must be scalars"
            );
        }

        switch ($this->driver->type($key)) {
            case 'string':
                $reply = $this->driver->get($key);
                $result = strpos($reply, $item) !== false;
                break;

            case 'list':
                $reply = $this->driver->lrange($key, 0, -1);
                $result = in_array($item, $reply);
                break;

            case 'set':
                $result = $this->driver->sismember($key, $item);
                break;

            case 'zset':
                $reply = $this->driver->zscore($key, $item);

                if (is_null($reply)) {
                    $result = false;
                } elseif (!is_null($itemValue)) {
                    $result = (float) $reply === (float) $itemValue;
                } else {
                    $result = true;
                }
                break;

            case 'hash':
                $reply = $this->driver->hget($key, $item);

                $result = is_null($itemValue)
                    ? !is_null($reply)
                    : (string) $reply === (string) $itemValue;
                break;

            case 'none':
                throw new ModuleException(
                    $this,
                    "Key \"$key\" does not exist"
                );
                break;
        }

        return $result;
    }

    
    private function checkKeyExists($key, $value = null)
    {
        $type = $this->driver->type($key);

        if (is_null($value)) {
            return $type != 'none';
        }

        $value = $this->boolToString($value);

        switch ($type) {
            case 'string':
                $reply = $this->driver->get($key);
                // Allow non strict equality (2 equals '2')
                $result = $reply == $value;
                break;

            case 'list':
                $reply = $this->driver->lrange($key, 0, -1);
                // Check both arrays have the same key/value pairs + same order
                $result = $reply === $value;
                break;

            case 'set':
                $reply = $this->driver->smembers($key);
                // Only check both arrays have the same values
                sort($reply);
                sort($value);
                $result = $reply === $value;
                break;

            case 'zset':
                $reply = $this->driver->zrange($key, 0, -1, 'WITHSCORES');
                // Check both arrays have the same key/value pairs + same order
                $reply = $this->scoresToFloat($reply);
                $value = $this->scoresToFloat($value);
                $result = $reply === $value;
                break;

            case 'hash':
                $reply = $this->driver->hgetall($key);
                // Only check both arrays have the same key/value pairs (==)
                $result = $reply == $value;
                break;

            default:
                $result = false;
        }

        return $result;
    }

    
    private function scoresToFloat(array $arr)
    {
        foreach ($arr as $member => $score) {
            $arr[$member] = (float) $score;
        }

        return $arr;
    }
}
