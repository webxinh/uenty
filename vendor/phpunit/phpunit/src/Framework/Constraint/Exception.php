<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_Exception extends PHPUnit_Framework_Constraint
{
    
    protected $className;

    
    public function __construct($className)
    {
        parent::__construct();
        $this->className = $className;
    }

    
    protected function matches($other)
    {
        return $other instanceof $this->className;
    }

    
    protected function failureDescription($other)
    {
        if ($other !== null) {
            $message = '';
            if ($other instanceof Exception || $other instanceof Throwable) {
                $message = '. Message was: "' . $other->getMessage() . '" at'
                        . "\n" . PHPUnit_Util_Filter::getFilteredStacktrace($other);
            }

            return sprintf(
                'exception of type "%s" matches expected exception "%s"%s',
                get_class($other),
                $this->className,
                $message
            );
        }

        return sprintf(
            'exception of type "%s" is thrown',
            $this->className
        );
    }

    
    public function toString()
    {
        return sprintf(
            'exception of type "%s"',
            $this->className
        );
    }
}
