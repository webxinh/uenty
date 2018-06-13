<?php
namespace Codeception\Lib\Actor\Shared;

use Codeception\Lib\Friend as LibFriend;
use Codeception\Scenario;

trait Friend
{
    protected $friends = [];

    
    abstract protected function getScenario();

    
    public function haveFriend($name, $actorClass = null)
    {
        if (!isset($this->friends[$name])) {
            $actor = $actorClass === null ? $this : new $actorClass($this->getScenario());
            $this->friends[$name] = new LibFriend($name, $actor, $this->getScenario()->current('modules'));
        }
        return $this->friends[$name];
    }
}
