<?php
namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Codeception\TestInterface;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Driver\AmazonSQS;
use Codeception\Lib\Driver\Beanstalk;
use Codeception\Lib\Driver\Iron;


class Queue extends CodeceptionModule
{
    
    public $queueDriver;

    
    public function _before(TestInterface $test)
    {
        $this->queueDriver->openConnection($this->config);
    }

    
    protected function validateConfig()
    {
        $this->queueDriver = $this->createQueueDriver();
        $this->requiredFields = $this->queueDriver->getRequiredConfig();
        $this->config = array_merge($this->queueDriver->getDefaultConfig(), $this->config);
        parent::validateConfig();
    }

    
    protected function createQueueDriver()
    {
        switch ($this->config['type']) {
            case 'aws':
            case 'sqs':
            case 'aws_sqs':
                return new AmazonSQS();
            case 'iron':
            case 'iron_mq':
                return new Iron();
            case 'beanstalk':
            case 'beanstalkd':
            case 'beanstalkq':
                return new Beanstalk();
            default:
                throw new ModuleConfigException(
                    __CLASS__,
                    "Unknown queue type {$this->config}; Supported queue types are: aws, iron, beanstalk"
                );
        }
    }

    // ----------- SEARCH METHODS BELOW HERE ------------------------//

    
    public function seeQueueExists($queue)
    {
        $this->assertContains($queue, $this->queueDriver->getQueues());
    }

    
    public function dontSeeQueueExists($queue)
    {
        $this->assertNotContains($queue, $this->queueDriver->getQueues());
    }

    
    public function seeEmptyQueue($queue)
    {
        $this->assertEquals(0, $this->queueDriver->getMessagesCurrentCountOnQueue($queue));
    }

    
    public function dontSeeEmptyQueue($queue)
    {
        $this->assertNotEquals(0, $this->queueDriver->getMessagesCurrentCountOnQueue($queue));
    }

    
    public function seeQueueHasCurrentCount($queue, $expected)
    {
        $this->assertEquals($expected, $this->queueDriver->getMessagesCurrentCountOnQueue($queue));
    }

    
    public function dontSeeQueueHasCurrentCount($queue, $expected)
    {
        $this->assertNotEquals($expected, $this->queueDriver->getMessagesCurrentCountOnQueue($queue));
    }

    
    public function seeQueueHasTotalCount($queue, $expected)
    {
        $this->assertEquals($expected, $this->queueDriver->getMessagesTotalCountOnQueue($queue));
    }

    
    public function dontSeeQueueHasTotalCount($queue, $expected)
    {
        $this->assertNotEquals($expected, $this->queueDriver->getMessagesTotalCountOnQueue($queue));
    }

    // ----------- UTILITY METHODS BELOW HERE -------------------------//

    
    public function addMessageToQueue($message, $queue)
    {
        $this->queueDriver->addMessageToQueue($message, $queue);
    }

    
    public function clearQueue($queue)
    {
        $this->queueDriver->clearQueue($queue);
    }

    // ----------- GRABBER METHODS BELOW HERE -----------------------//

    
    public function grabQueues()
    {
        return $this->queueDriver->getQueues();
    }

    
    public function grabQueueCurrentCount($queue)
    {
        return $this->queueDriver->getMessagesCurrentCountOnQueue($queue);
    }

    
    public function grabQueueTotalCount($queue)
    {
        return $this->queueDriver->getMessagesTotalCountOnQueue($queue);
    }
}
