<?php

/*
 * This file is part of the Prophecy.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *     Marcello Duarte <marcello.duarte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prophecy\Call;

use Exception;


class Call
{
    private $methodName;
    private $arguments;
    private $returnValue;
    private $exception;
    private $file;
    private $line;

    
    public function __construct($methodName, array $arguments, $returnValue,
                                Exception $exception = null, $file, $line)
    {
        $this->methodName  = $methodName;
        $this->arguments   = $arguments;
        $this->returnValue = $returnValue;
        $this->exception   = $exception;

        if ($file) {
            $this->file = $file;
            $this->line = intval($line);
        }
    }

    
    public function getMethodName()
    {
        return $this->methodName;
    }

    
    public function getArguments()
    {
        return $this->arguments;
    }

    
    public function getReturnValue()
    {
        return $this->returnValue;
    }

    
    public function getException()
    {
        return $this->exception;
    }

    
    public function getFile()
    {
        return $this->file;
    }

    
    public function getLine()
    {
        return $this->line;
    }

    
    public function getCallPlace()
    {
        if (null === $this->file) {
            return 'unknown';
        }

        return sprintf('%s:%d', $this->file, $this->line);
    }
}
