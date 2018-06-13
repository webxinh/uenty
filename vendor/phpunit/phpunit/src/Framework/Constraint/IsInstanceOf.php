<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_IsInstanceOf extends PHPUnit_Framework_Constraint
{
    
    protected $className;

    
    public function __construct($className)
    {
        parent::__construct();
        $this->className = $className;
    }

    
    protected function matches($other)
    {
        return ($other instanceof $this->className);
    }

    
    protected function failureDescription($other)
    {
        return sprintf(
            '%s is an instance of %s "%s"',
            $this->exporter->shortenedExport($other),
            $this->getType(),
            $this->className
        );
    }

    
    public function toString()
    {
        return sprintf(
            'is instance of %s "%s"',
            $this->getType(),
            $this->className
        );
    }

    private function getType()
    {
        try {
            $reflection = new ReflectionClass($this->className);
            if ($reflection->isInterface()) {
                return 'interface';
            }
        } catch (ReflectionException $e) {
        }

        return 'class';
    }
}
