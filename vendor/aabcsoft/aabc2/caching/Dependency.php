<?php


namespace aabc\caching;


abstract class Dependency extends \aabc\base\Object
{
    
    public $data;
    
    public $reusable = false;

    
    private static $_reusableData = [];


    
    public function evaluateDependency($cache)
    {
        if ($this->reusable) {
            $hash = $this->generateReusableHash();
            if (!array_key_exists($hash, self::$_reusableData)) {
                self::$_reusableData[$hash] = $this->generateDependencyData($cache);
            }
            $this->data = self::$_reusableData[$hash];
        } else {
            $this->data = $this->generateDependencyData($cache);
        }
    }

    
    public function getHasChanged($cache)
    {
        return $this->isChanged($cache);
    }

    
    public function isChanged($cache)
    {
        if ($this->reusable) {
            $hash = $this->generateReusableHash();
            if (!array_key_exists($hash, self::$_reusableData)) {
                self::$_reusableData[$hash] = $this->generateDependencyData($cache);
            }
            $data = self::$_reusableData[$hash];
        } else {
            $data = $this->generateDependencyData($cache);
        }
        return $data !== $this->data;
    }

    
    public static function resetReusableData()
    {
        self::$_reusableData = [];
    }

    
    protected function generateReusableHash()
    {
        $data = $this->data;
        $this->data = null;  // https://github.com/aabcsoft/aabc2/issues/3052
        $key = sha1(serialize($this));
        $this->data = $data;
        return $key;
    }

    
    abstract protected function generateDependencyData($cache);
}
