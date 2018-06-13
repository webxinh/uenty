<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Constraint_StringContains extends PHPUnit_Framework_Constraint
{
    
    protected $string;

    
    protected $ignoreCase;

    
    public function __construct($string, $ignoreCase = false)
    {
        parent::__construct();

        $this->string     = $string;
        $this->ignoreCase = $ignoreCase;
    }

    
    protected function matches($other)
    {
        if ($this->ignoreCase) {
            return mb_stripos($other, $this->string) !== false;
        } else {
            return mb_strpos($other, $this->string) !== false;
        }
    }

    
    public function toString()
    {
        if ($this->ignoreCase) {
            $string = mb_strtolower($this->string);
        } else {
            $string = $this->string;
        }

        return sprintf(
            'contains "%s"',
            $string
        );
    }
}
