<?php
namespace Codeception\Event;

use Codeception\Step;
use Codeception\TestInterface;
use Symfony\Component\EventDispatcher\Event;

class StepEvent extends Event
{
    
    protected $step;

    
    protected $test;

    public function __construct(TestInterface $test, Step $step)
    {
        $this->test = $test;
        $this->step = $step;
    }

    public function getStep()
    {
        return $this->step;
    }

    
    public function getTest()
    {
        return $this->test;
    }
}
