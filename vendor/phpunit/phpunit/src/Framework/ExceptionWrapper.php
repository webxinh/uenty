<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_ExceptionWrapper extends PHPUnit_Framework_Exception
{
    
    protected $className;

    
    protected $previous;

    
    public function __construct($e)
    {
        // PDOException::getCode() is a string.
        // @see http://php.net/manual/en/class.pdoexception.php#95812
        parent::__construct($e->getMessage(), (int) $e->getCode());

        $this->className = get_class($e);
        $this->file      = $e->getFile();
        $this->line      = $e->getLine();

        $this->serializableTrace = $e->getTrace();

        foreach ($this->serializableTrace as $i => $call) {
            unset($this->serializableTrace[$i]['args']);
        }

        if ($e->getPrevious()) {
            $this->previous = new self($e->getPrevious());
        }
    }

    
    public function getClassName()
    {
        return $this->className;
    }

    
    public function getPreviousWrapped()
    {
        return $this->previous;
    }

    
    public function __toString()
    {
        $string = PHPUnit_Framework_TestFailure::exceptionToString($this);

        if ($trace = PHPUnit_Util_Filter::getFilteredStacktrace($this)) {
            $string .= "\n" . $trace;
        }

        if ($this->previous) {
            $string .= "\nCaused by\n" . $this->previous;
        }

        return $string;
    }
}
