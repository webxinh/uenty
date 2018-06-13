<?php
namespace Codeception\Event;

use Symfony\Component\EventDispatcher\Event;

class PrintResultEvent extends Event
{
    
    protected $result;

    
    protected $printer;

    public function __construct(\PHPUnit_Framework_TestResult $result, \PHPUnit_Util_Printer $printer)
    {
        $this->result = $result;
        $this->printer = $printer;
    }

    
    public function getPrinter()
    {
        return $this->printer;
    }

    
    public function getResult()
    {
        return $this->result;
    }
}
