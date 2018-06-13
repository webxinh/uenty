<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_ObjectHasAttribute extends PHPUnit_Framework_Constraint_ClassHasAttribute
{
    
    protected function matches($other)
    {
        $object = new ReflectionObject($other);

        return $object->hasProperty($this->attributeName);
    }
}
