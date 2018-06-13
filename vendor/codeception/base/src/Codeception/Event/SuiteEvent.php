<?php
namespace Codeception\Event;

use Codeception\Suite;
use Symfony\Component\EventDispatcher\Event;

class SuiteEvent extends Event
{
    
    protected $suite;

    
    protected $result;

    
    protected $settings;

    public function __construct(
        \PHPUnit_Framework_TestSuite $suite,
        \PHPUnit_Framework_TestResult $result = null,
        $settings = []
    ) {
        $this->suite = $suite;
        $this->result = $result;
        $this->settings = $settings;
    }

    
    public function getSuite()
    {
        return $this->suite;
    }

    
    public function getResult()
    {
        return $this->result;
    }

    public function getSettings()
    {
        return $this->settings;
    }
}
