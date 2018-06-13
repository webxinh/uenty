<?php

namespace DeepCopy;

use DeepCopy\Exception\CloneException;
use DeepCopy\Filter\Filter;
use DeepCopy\Matcher\Matcher;
use DeepCopy\TypeFilter\Spl\SplDoublyLinkedList;
use DeepCopy\TypeFilter\TypeFilter;
use DeepCopy\TypeMatcher\TypeMatcher;
use ReflectionProperty;
use DeepCopy\Reflection\ReflectionHelper;


class DeepCopy
{
    
    private $hashMap = [];

    
    private $filters = [];

    
    private $typeFilters = [];

    private $skipUncloneable = false;

    
    private $useCloneMethod;

    
    public function __construct($useCloneMethod = false)
    {
        $this->useCloneMethod = $useCloneMethod;

        $this->addTypeFilter(new SplDoublyLinkedList($this), new TypeMatcher('\SplDoublyLinkedList'));
    }

    
    public function skipUncloneable($skipUncloneable = true)
    {
        $this->skipUncloneable = $skipUncloneable;
        return $this;
    }

    
    public function copy($object)
    {
        $this->hashMap = [];

        return $this->recursiveCopy($object);
    }

    public function addFilter(Filter $filter, Matcher $matcher)
    {
        $this->filters[] = [
            'matcher' => $matcher,
            'filter'  => $filter,
        ];
    }

    public function addTypeFilter(TypeFilter $filter, TypeMatcher $matcher)
    {
        $this->typeFilters[] = [
            'matcher' => $matcher,
            'filter'  => $filter,
        ];
    }


    private function recursiveCopy($var)
    {
        // Matches Type Filter
        if ($filter = $this->getFirstMatchedTypeFilter($this->typeFilters, $var)) {
            return $filter->apply($var);
        }

        // Resource
        if (is_resource($var)) {
            return $var;
        }
        // Array
        if (is_array($var)) {
            return $this->copyArray($var);
        }
        // Scalar
        if (! is_object($var)) {
            return $var;
        }
        // Object
        return $this->copyObject($var);
    }

    
    private function copyArray(array $array)
    {
        foreach ($array as $key => $value) {
            $array[$key] = $this->recursiveCopy($value);
        }

        return $array;
    }

    
    private function copyObject($object)
    {
        $objectHash = spl_object_hash($object);

        if (isset($this->hashMap[$objectHash])) {
            return $this->hashMap[$objectHash];
        }

        $reflectedObject = new \ReflectionObject($object);

        if (false === $isCloneable = $reflectedObject->isCloneable() and $this->skipUncloneable) {
            $this->hashMap[$objectHash] = $object;
            return $object;
        }

        if (false === $isCloneable) {
            throw new CloneException(sprintf(
                'Class "%s" is not cloneable.',
                $reflectedObject->getName()
            ));
        }

        $newObject = clone $object;
        $this->hashMap[$objectHash] = $newObject;
        if ($this->useCloneMethod && $reflectedObject->hasMethod('__clone')) {
            return $object;
        }

        if ($newObject instanceof \DateTimeInterface) {
            return $newObject;
        }
        foreach (ReflectionHelper::getProperties($reflectedObject) as $property) {
            $this->copyObjectProperty($newObject, $property);
        }

        return $newObject;
    }

    private function copyObjectProperty($object, ReflectionProperty $property)
    {
        // Ignore static properties
        if ($property->isStatic()) {
            return;
        }

        // Apply the filters
        foreach ($this->filters as $item) {
            
            $matcher = $item['matcher'];
            
            $filter = $item['filter'];

            if ($matcher->matches($object, $property->getName())) {
                $filter->apply(
                    $object,
                    $property->getName(),
                    function ($object) {
                        return $this->recursiveCopy($object);
                    }
                );
                // If a filter matches, we stop processing this property
                return;
            }
        }

        $property->setAccessible(true);
        $propertyValue = $property->getValue($object);

        // Copy the property
        $property->setValue($object, $this->recursiveCopy($propertyValue));
    }

    
    private function getFirstMatchedTypeFilter(array $filterRecords, $var)
    {
        $matched = $this->first(
            $filterRecords,
            function (array $record) use ($var) {
                /* @var TypeMatcher $matcher */
                $matcher = $record['matcher'];

                return $matcher->matches($var);
            }
        );

        return isset($matched) ? $matched['filter'] : null;
    }

    
    private function first(array $elements, callable $predicate)
    {
        foreach ($elements as $element) {
            if (call_user_func($predicate, $element)) {
                return $element;
            }
        }

        return null;
    }
}
