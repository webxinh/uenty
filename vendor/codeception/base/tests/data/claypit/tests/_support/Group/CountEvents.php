<?php
namespace Group;

use \Codeception\Event\TestEvent;


class CountEvents extends \Codeception\GroupObject
{
    public static $group = 'countevents';
    public static $beforeCount = 0;
    public static $afterCount = 0;

    public function _before(TestEvent $e)
    {
        $this::$beforeCount++;
        $this->writeln("Group Before Events: " . $this::$beforeCount);
    }

    public function _after(TestEvent $e)
    {
        $this::$afterCount++;
        $this->writeln("Group After Events: " . $this::$afterCount);
    }
}
