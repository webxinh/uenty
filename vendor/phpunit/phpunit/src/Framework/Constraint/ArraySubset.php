<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_ArraySubset extends PHPUnit_Framework_Constraint
{
    
    protected $subset;

    
    protected $strict;

    
    public function __construct($subset, $strict = false)
    {
        parent::__construct();
        $this->strict = $strict;
        $this->subset = $subset;
    }

    
    protected function matches($other)
    {
        //type cast $other & $this->subset as an array to allow 
        //support in standard array functions.
        if($other instanceof ArrayAccess) {
            $other = (array) $other;
        }

        if($this->subset instanceof ArrayAccess) {
            $this->subset = (array) $this->subset;
        }

        $patched = array_replace_recursive($other, $this->subset);

        if ($this->strict) {
            return $other === $patched;
        } else {
            return $other == $patched;
        }
    }

    
    public function toString()
    {
        return 'has the subset ' . $this->exporter->export($this->subset);
    }

    
    protected function failureDescription($other)
    {
        return 'an array ' . $this->toString();
    }
}
