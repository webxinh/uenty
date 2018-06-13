<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_SyntheticError extends PHPUnit_Framework_AssertionFailedError
{
    
    protected $syntheticFile = '';

    
    protected $syntheticLine = 0;

    
    protected $syntheticTrace = [];

    
    public function __construct($message, $code, $file, $line, $trace)
    {
        parent::__construct($message, $code);

        $this->syntheticFile  = $file;
        $this->syntheticLine  = $line;
        $this->syntheticTrace = $trace;
    }

    
    public function getSyntheticFile()
    {
        return $this->syntheticFile;
    }

    
    public function getSyntheticLine()
    {
        return $this->syntheticLine;
    }

    
    public function getSyntheticTrace()
    {
        return $this->syntheticTrace;
    }
}
