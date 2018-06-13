<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_ClassHasAttribute extends PHPUnit_Framework_Constraint
{
    
    protected $attributeName;

    
    public function __construct($attributeName)
    {
        parent::__construct();
        $this->attributeName = $attributeName;
    }

    
    protected function matches($other)
    {
        $class = new ReflectionClass($other);

        return $class->hasProperty($this->attributeName);
    }

    
    public function toString()
    {
        return sprintf(
            'has attribute "%s"',
            $this->attributeName
        );
    }

    
    protected function failureDescription($other)
    {
        return sprintf(
            '%sclass "%s" %s',
            is_object($other) ? 'object of ' : '',
            is_object($other) ? get_class($other) : $other,
            $this->toString()
        );
    }
}
