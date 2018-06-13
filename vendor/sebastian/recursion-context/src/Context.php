<?php
/*
 * This file is part of the Recursion Context package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\RecursionContext;


final class Context
{
    
    private $arrays;

    
    private $objects;

    
    public function __construct()
    {
        $this->arrays  = array();
        $this->objects = new \SplObjectStorage;
    }

    
    public function add(&$value)
    {
        if (is_array($value)) {
            return $this->addArray($value);
        } elseif (is_object($value)) {
            return $this->addObject($value);
        }

        throw new InvalidArgumentException(
            'Only arrays and objects are supported'
        );
    }

    
    public function contains(&$value)
    {
        if (is_array($value)) {
            return $this->containsArray($value);
        } elseif (is_object($value)) {
            return $this->containsObject($value);
        }

        throw new InvalidArgumentException(
            'Only arrays and objects are supported'
        );
    }

    
    private function addArray(array &$array)
    {
        $key = $this->containsArray($array);

        if ($key !== false) {
            return $key;
        }

        $key            = count($this->arrays);
        $this->arrays[] = &$array;

        if (!isset($array[PHP_INT_MAX]) && !isset($array[PHP_INT_MAX - 1])) {
            $array[] = $key;
            $array[] = $this->objects;
        } else { /* cover the improbable case too */
            do {
                $key = random_int(PHP_INT_MIN, PHP_INT_MAX);
            } while (isset($array[$key]));

            $array[$key] = $key;

            do {
                $key = random_int(PHP_INT_MIN, PHP_INT_MAX);
            } while (isset($array[$key]));

            $array[$key] = $this->objects;
        }

        return $key;
    }

    
    private function addObject($object)
    {
        if (!$this->objects->contains($object)) {
            $this->objects->attach($object);
        }

        return spl_object_hash($object);
    }

    
    private function containsArray(array &$array)
    {
        $end = array_slice($array, -2);

        return isset($end[1]) && $end[1] === $this->objects ? $end[0] : false;
    }

    
    private function containsObject($value)
    {
        if ($this->objects->contains($value)) {
            return spl_object_hash($value);
        }

        return false;
    }

    public function __destruct()
    {
        foreach ($this->arrays as &$array) {
            if (is_array($array)) {
                array_pop($array);
                array_pop($array);
            }
        }
    }
}
