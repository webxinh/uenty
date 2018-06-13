<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_ClassHasStaticAttribute extends PHPUnit_Framework_Constraint_ClassHasAttribute
{
    
    protected function matches($other)
    {
        $class = new ReflectionClass($other);

        if ($class->hasProperty($this->attributeName)) {
            $attribute = $class->getProperty($this->attributeName);

            return $attribute->isStatic();
        } else {
            return false;
        }
    }

    
    public function toString()
    {
        return sprintf(
            'has static attribute "%s"',
            $this->attributeName
        );
    }
}
