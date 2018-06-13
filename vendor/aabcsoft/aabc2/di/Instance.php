<?php


namespace aabc\di;

use Aabc;
use aabc\base\InvalidConfigException;


class Instance
{
    
    public $id;


    
    protected function __construct($id)
    {
        $this->id = $id;
    }

    
    public static function of($id)
    {
        return new static($id);
    }

    
    public static function ensure($reference, $type = null, $container = null)
    {
        if (is_array($reference)) {
            $class = isset($reference['class']) ? $reference['class'] : $type;
            if (!$container instanceof Container) {
                $container = Aabc::$container;
            }
            unset($reference['class']);
            return $container->get($class, [], $reference);
        } elseif (empty($reference)) {
            throw new InvalidConfigException('The required component is not specified.');
        }

        if (is_string($reference)) {
            $reference = new static($reference);
        } elseif ($type === null || $reference instanceof $type) {
            return $reference;
        }

        if ($reference instanceof self) {
            try {
                $component = $reference->get($container);
            } catch(\ReflectionException $e) {
                throw new InvalidConfigException('Failed to instantiate component or class "' . $reference->id . '".', 0, $e);
            }
            if ($type === null || $component instanceof $type) {
                return $component;
            } else {
                throw new InvalidConfigException('"' . $reference->id . '" refers to a ' . get_class($component) . " component. $type is expected.");
            }
        }

        $valueType = is_object($reference) ? get_class($reference) : gettype($reference);
        throw new InvalidConfigException("Invalid data type: $valueType. $type is expected.");
    }

    
    public function get($container = null)
    {
        if ($container) {
            return $container->get($this->id);
        }
        if (Aabc::$app && Aabc::$app->has($this->id)) {
            return Aabc::$app->get($this->id);
        } else {
            return Aabc::$container->get($this->id);
        }
    }
}
