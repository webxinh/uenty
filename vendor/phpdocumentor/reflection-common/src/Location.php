<?php


namespace phpDocumentor\Reflection;


final class Location
{
    
    private $lineNumber = 0;

    
    private $columnNumber = 0;

    
    public function __construct($lineNumber, $columnNumber = 0)
    {
        $this->lineNumber   = $lineNumber;
        $this->columnNumber = $columnNumber;
    }

    
    public function getLineNumber()
    {
        return $this->lineNumber;
    }

    
    public function getColumnNumber()
    {
        return $this->columnNumber;
    }
}
