<?php


namespace aabc\caching;

use Aabc;
use aabc\base\Component;
use aabc\helpers\StringHelper;


abstract class Cache extends Component implements \ArrayAccess
{
    
    public $keyPrefix;
    
    public $serializer;
    
    public $defaultDuration = 0;


    
    public function buildKey($key)
    {
        if (is_string($key)) {
            $key = ctype_alnum($key) && StringHelper::byteLength($key) <= 32 ? $key : md5($key);
        } else {
            $key = md5(json_encode($key));
        }

        return $this->keyPrefix . $key;
    }

    
    public function get($key)
    {
        $key = $this->buildKey($key);
        $value = $this->getValue($key);
        if ($value === false || $this->serializer === false) {
            return $value;
        } elseif ($this->serializer === null) {
            $value = unserialize($value);
        } else {
            $value = call_user_func($this->serializer[1], $value);
        }
        if (is_array($value) && !($value[1] instanceof Dependency && $value[1]->isChanged($this))) {
            return $value[0];
        } else {
            return false;
        }
    }

    
    public function exists($key)
    {
        $key = $this->buildKey($key);
        $value = $this->getValue($key);

        return $value !== false;
    }

    
    public function mget($keys)
    {
        return $this->multiGet($keys);
    }

    
    public function multiGet($keys)
    {
        $keyMap = [];
        foreach ($keys as $key) {
            $keyMap[$key] = $this->buildKey($key);
        }
        $values = $this->getValues(array_values($keyMap));
        $results = [];
        foreach ($keyMap as $key => $newKey) {
            $results[$key] = false;
            if (isset($values[$newKey])) {
                if ($this->serializer === false) {
                    $results[$key] = $values[$newKey];
                } else {
                    $value = $this->serializer === null ? unserialize($values[$newKey])
                        : call_user_func($this->serializer[1], $values[$newKey]);

                    if (is_array($value) && !($value[1] instanceof Dependency && $value[1]->isChanged($this))) {
                        $results[$key] = $value[0];
                    }
                }
            }
        }

        return $results;
    }

    
    public function set($key, $value, $duration = null, $dependency = null)
    {
        if ($duration === null) {
            $duration = $this->defaultDuration;
        }

        if ($dependency !== null && $this->serializer !== false) {
            $dependency->evaluateDependency($this);
        }
        if ($this->serializer === null) {
            $value = serialize([$value, $dependency]);
        } elseif ($this->serializer !== false) {
            $value = call_user_func($this->serializer[0], [$value, $dependency]);
        }
        $key = $this->buildKey($key);

        return $this->setValue($key, $value, $duration);
    }

    
    public function mset($items, $duration = 0, $dependency = null)
    {
        return $this->multiSet($items, $duration, $dependency);
    }

    
    public function multiSet($items, $duration = 0, $dependency = null)
    {
        if ($dependency !== null && $this->serializer !== false) {
            $dependency->evaluateDependency($this);
        }

        $data = [];
        foreach ($items as $key => $value) {
            if ($this->serializer === null) {
                $value = serialize([$value, $dependency]);
            } elseif ($this->serializer !== false) {
                $value = call_user_func($this->serializer[0], [$value, $dependency]);
            }

            $key = $this->buildKey($key);
            $data[$key] = $value;
        }

        return $this->setValues($data, $duration);
    }

    
    public function madd($items, $duration = 0, $dependency = null)
    {
        return $this->multiAdd($items, $duration, $dependency);
    }

    
    public function multiAdd($items, $duration = 0, $dependency = null)
    {
        if ($dependency !== null && $this->serializer !== false) {
            $dependency->evaluateDependency($this);
        }

        $data = [];
        foreach ($items as $key => $value) {
            if ($this->serializer === null) {
                $value = serialize([$value, $dependency]);
            } elseif ($this->serializer !== false) {
                $value = call_user_func($this->serializer[0], [$value, $dependency]);
            }

            $key = $this->buildKey($key);
            $data[$key] = $value;
        }

        return $this->addValues($data, $duration);
    }

    
    public function add($key, $value, $duration = 0, $dependency = null)
    {
        if ($dependency !== null && $this->serializer !== false) {
            $dependency->evaluateDependency($this);
        }
        if ($this->serializer === null) {
            $value = serialize([$value, $dependency]);
        } elseif ($this->serializer !== false) {
            $value = call_user_func($this->serializer[0], [$value, $dependency]);
        }
        $key = $this->buildKey($key);

        return $this->addValue($key, $value, $duration);
    }

    
    public function delete($key)
    {
        $key = $this->buildKey($key);

        return $this->deleteValue($key);
    }

    
    public function flush()
    {
        return $this->flushValues();
    }

    
    abstract protected function getValue($key);

    
    abstract protected function setValue($key, $value, $duration);

    
    abstract protected function addValue($key, $value, $duration);

    
    abstract protected function deleteValue($key);

    
    abstract protected function flushValues();

    
    protected function getValues($keys)
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->getValue($key);
        }

        return $results;
    }

    
    protected function setValues($data, $duration)
    {
        $failedKeys = [];
        foreach ($data as $key => $value) {
            if ($this->setValue($key, $value, $duration) === false) {
                $failedKeys[] = $key;
            }
        }

        return $failedKeys;
    }

    
    protected function addValues($data, $duration)
    {
        $failedKeys = [];
        foreach ($data as $key => $value) {
            if ($this->addValue($key, $value, $duration) === false) {
                $failedKeys[] = $key;
            }
        }

        return $failedKeys;
    }

    
    public function offsetExists($key)
    {
        return $this->get($key) !== false;
    }

    
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    
    public function offsetUnset($key)
    {
        $this->delete($key);
    }

    
    public function getOrSet($key, \Closure $closure, $duration = null, $dependency = null)
    {
        if (($value = $this->get($key)) !== false) {
            return $value;
        }

        $value = call_user_func($closure, $this);
        if (!$this->set($key, $value, $duration, $dependency)) {
            Aabc::warning('Failed to set cache value for key ' . json_encode($value), __METHOD__);
        }

        return $value;
    }
}
