<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_Exception extends RuntimeException implements PHPUnit_Exception
{
    
    protected $serializableTrace;

    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->serializableTrace = $this->getTrace();
        foreach ($this->serializableTrace as $i => $call) {
            unset($this->serializableTrace[$i]['args']);
        }
    }

    
    public function getSerializableTrace()
    {
        return $this->serializableTrace;
    }

    
    public function __toString()
    {
        $string = PHPUnit_Framework_TestFailure::exceptionToString($this);

        if ($trace = PHPUnit_Util_Filter::getFilteredStacktrace($this)) {
            $string .= "\n" . $trace;
        }

        return $string;
    }

    public function __sleep()
    {
        return array_keys(get_object_vars($this));
    }
}
