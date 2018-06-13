<?php
namespace Codeception\Util;


class Annotation
{
    protected static $reflectedClasses = [];
    protected static $regex = '/@%s(?:[ \t]*(.*?))?[ \t]*\r?$/m';
    protected static $lastReflected = null;

    
    protected $reflectedClass;

    protected $currentReflectedItem;

    
    public static function forClass($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (!isset(static::$reflectedClasses[$class])) {
            static::$reflectedClasses[$class] = new \ReflectionClass($class);
        }

        return new static(static::$reflectedClasses[$class]);
    }

    
    public static function forMethod($class, $method)
    {
        return self::forClass($class)->method($method);
    }

    
    public static function fetchAllFromComment($annotation, $comment)
    {
        if (preg_match_all(sprintf(self::$regex, $annotation), $comment, $matched)) {
            return $matched[1];
        }
        return [];
    }

    public function __construct(\ReflectionClass $class)
    {
        $this->currentReflectedItem = $this->reflectedClass = $class;
    }

    
    public function method($method)
    {
        $this->currentReflectedItem = $this->reflectedClass->getMethod($method);
        return $this;
    }

    
    public function fetch($annotation)
    {
        $docBlock = $this->currentReflectedItem->getDocComment();
        if (preg_match(sprintf(self::$regex, $annotation), $docBlock, $matched)) {
            return $matched[1];
        }
        return null;
    }

    
    public function fetchAll($annotation)
    {
        $docBlock = $this->currentReflectedItem->getDocComment();
        if (preg_match_all(sprintf(self::$regex, $annotation), $docBlock, $matched)) {
            return $matched[1];
        }
        return [];
    }

    public function raw()
    {
        return $this->currentReflectedItem->getDocComment();
    }

    
    public static function arrayValue($annotation)
    {
        $annotation = trim($annotation);
        $openingBrace = substr($annotation, 0, 1);

        // json-style data format
        if (in_array($openingBrace, ['{', '['])) {
            return json_decode($annotation, true);
        }

        // doctrine-style data format
        if ($openingBrace === '(') {
            preg_match_all('~(\w+)\s*?=\s*?"(.*?)"\s*?[,)]~', $annotation, $matches, PREG_SET_ORDER);
            $data = [];
            foreach ($matches as $item) {
                $data[$item[1]] = $item[2];
            }
            return $data;
        }
        return null;
    }
}
