<?php
namespace Codeception\Event;

use Symfony\Component\EventDispatcher\Event;

class TestEvent extends Event
{
    
    protected $test;

    
    protected $time;

    public function __construct(\PHPUnit_Framework_Test $test, $time = 0)
    {
        $this->test = $test;
        $this->time = $time;
    }

    
    public function getTime()
    {
        return $this->time;
    }

    
    public function getTest()
    {
        return $this->test;
    }
}
