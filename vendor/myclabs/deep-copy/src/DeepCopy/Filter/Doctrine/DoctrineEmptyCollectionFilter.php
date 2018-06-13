<?php

namespace DeepCopy\Filter\Doctrine;

use DeepCopy\Filter\Filter;
use Doctrine\Common\Collections\ArrayCollection;

class DoctrineEmptyCollectionFilter implements Filter
{
    
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = new \ReflectionProperty($object, $property);
        $reflectionProperty->setAccessible(true);

        $reflectionProperty->setValue($object, new ArrayCollection());
    }
} 