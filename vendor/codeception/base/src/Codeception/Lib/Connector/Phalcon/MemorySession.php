<?php
namespace Codeception\Lib\Connector\Phalcon;

use Phalcon\Session\AdapterInterface;

class MemorySession implements AdapterInterface
{
    
    protected $sessionId;

    
    protected $name;

    
    protected $started = false;

    
    protected $memory = [];

    
    protected $options = [];

    public function __construct(array $options = null)
    {
        $this->sessionId = $this->generateId();

        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    
    public function start()
    {
        if ($this->status() !== PHP_SESSION_ACTIVE) {
            $this->memory = [];
            $this->started = true;

            return true;
        }

        return false;
    }

    
    public function setOptions(array $options)
    {
        if (isset($options['uniqueId'])) {
            $this->sessionId = $options['uniqueId'];
        }

        $this->options = $options;
    }

    
    public function getOptions()
    {
        return $this->options;
    }

    
    public function get($index, $defaultValue = null, $remove = false)
    {
        $key = $this->prepareIndex($index);

        if (!isset($this->memory[$key])) {
            return $defaultValue;
        }

        $return = $this->memory[$key];

        if ($remove) {
            unset($this->memory[$key]);
        }

        return $return;
    }

    
    public function set($index, $value)
    {
        $this->memory[$this->prepareIndex($index)] = $value;
    }

    
    public function has($index)
    {
        return isset($this->memory[$this->prepareIndex($index)]);
    }

    
    public function remove($index)
    {
        unset($this->memory[$this->prepareIndex($index)]);
    }

    
    public function getId()
    {
        return $this->sessionId;
    }

    
    public function isStarted()
    {
        return $this->started;
    }

    
    public function status()
    {
        if ($this->isStarted()) {
            return PHP_SESSION_ACTIVE;
        }

        return PHP_SESSION_NONE;
    }

    
    public function destroy($removeData = false)
    {
        if ($removeData) {
            if (!empty($this->sessionId)) {
                foreach ($this->memory as $key => $value) {
                    if (0 === strpos($key, $this->sessionId . '#')) {
                        unset($this->memory[$key]);
                    }
                }
            } else {
                $this->memory = [];
            }
        }

        $this->started = false;

        return true;
    }

    
    public function regenerateId($deleteOldSession = true)
    {
        $this->sessionId = $this->generateId();

        return $this;
    }

    
    public function setName($name)
    {
        $this->name = $name;
    }

    
    public function getName()
    {
        return $this->name;
    }

    
    public function toArray()
    {
        return (array) $this->memory;
    }

    
    public function __get($index)
    {
        return $this->get($index);
    }

    
    public function __set($index, $value)
    {
        $this->set($index, $value);
    }

    
    public function __isset($index)
    {
        return $this->has($index);
    }

    
    public function __unset($index)
    {
        $this->remove($index);
    }

    private function prepareIndex($index)
    {
        if ($this->sessionId) {
            $key = $this->sessionId . '#' . $index;
        } else {
            $key = $index;
        }

        return $key;
    }

    
    private function generateId()
    {
        return md5(time());
    }
}
