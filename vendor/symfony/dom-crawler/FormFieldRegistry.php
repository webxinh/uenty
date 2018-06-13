<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DomCrawler;

use Symfony\Component\DomCrawler\Field\FormField;


class FormFieldRegistry
{
    private $fields = array();

    private $base;

    
    public function add(FormField $field)
    {
        $segments = $this->getSegments($field->getName());

        $target = &$this->fields;
        while ($segments) {
            if (!is_array($target)) {
                $target = array();
            }
            $path = array_shift($segments);
            if ('' === $path) {
                $target = &$target[];
            } else {
                $target = &$target[$path];
            }
        }
        $target = $field;
    }

    
    public function remove($name)
    {
        $segments = $this->getSegments($name);
        $target = &$this->fields;
        while (count($segments) > 1) {
            $path = array_shift($segments);
            if (!array_key_exists($path, $target)) {
                return;
            }
            $target = &$target[$path];
        }
        unset($target[array_shift($segments)]);
    }

    
    public function &get($name)
    {
        $segments = $this->getSegments($name);
        $target = &$this->fields;
        while ($segments) {
            $path = array_shift($segments);
            if (!array_key_exists($path, $target)) {
                throw new \InvalidArgumentException(sprintf('Unreachable field "%s"', $path));
            }
            $target = &$target[$path];
        }

        return $target;
    }

    
    public function has($name)
    {
        try {
            $this->get($name);

            return true;
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }

    
    public function set($name, $value)
    {
        $target = &$this->get($name);
        if ((!is_array($value) && $target instanceof Field\FormField) || $target instanceof Field\ChoiceFormField) {
            $target->setValue($value);
        } elseif (is_array($value)) {
            $fields = self::create($name, $value);
            foreach ($fields->all() as $k => $v) {
                $this->set($k, $v);
            }
        } else {
            throw new \InvalidArgumentException(sprintf('Cannot set value on a compound field "%s".', $name));
        }
    }

    
    public function all()
    {
        return $this->walk($this->fields, $this->base);
    }

    
    private static function create($base, array $values)
    {
        $registry = new static();
        $registry->base = $base;
        $registry->fields = $values;

        return $registry;
    }

    
    private function walk(array $array, $base = '', array &$output = array())
    {
        foreach ($array as $k => $v) {
            $path = empty($base) ? $k : sprintf('%s[%s]', $base, $k);
            if (is_array($v)) {
                $this->walk($v, $path, $output);
            } else {
                $output[$path] = $v;
            }
        }

        return $output;
    }

    
    private function getSegments($name)
    {
        if (preg_match('/^(?P<base>[^[]+)(?P<extra>(\[.*)|$)/', $name, $m)) {
            $segments = array($m['base']);
            while (!empty($m['extra'])) {
                $extra = $m['extra'];
                if (preg_match('/^\[(?P<segment>.*?)\](?P<extra>.*)$/', $extra, $m)) {
                    $segments[] = $m['segment'];
                } else {
                    $segments[] = $extra;
                }
            }

            return $segments;
        }

        return array($name);
    }
}
