<?php
namespace Codeception;

use Codeception\Lib\Actor\Shared\Comment;
use Codeception\Lib\Actor\Shared\Friend;
use Codeception\Step\Executor;

abstract class Actor
{
    use Comment;
    use Friend;

    
    protected $scenario;

    public function __construct(Scenario $scenario)
    {
        $this->scenario = $scenario;
    }

    
    protected function getScenario()
    {
        return $this->scenario;
    }

    public function wantToTest($text)
    {
        $this->wantTo('test ' . $text);
    }

    public function wantTo($text)
    {
        $this->scenario->setFeature(mb_strtolower($text, 'utf-8'));
    }

    public function __call($method, $arguments)
    {
        $class = get_class($this);
        throw new \RuntimeException("Call to undefined method $class::$method");
    }
    
    
    public function execute($callable)
    {
        $this->scenario->addStep(new Executor($callable, []));
        $callable();
        return $this;
    }
}
