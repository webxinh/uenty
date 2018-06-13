<?php
namespace Codeception\Lib\Driver;

use Codeception\Lib\Interfaces\Queue;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Exception\ConnectionException;

class Beanstalk implements Queue
{

    
    protected $queue;

    public function openConnection($config)
    {
        $this->queue = new Pheanstalk($config['host'], $config['port'], $config['timeout']);
    }

    
    public function addMessageToQueue($message, $queue)
    {
        $this->queue->putInTube($queue, $message);
    }

    
    public function getMessagesTotalCountOnQueue($queue)
    {
        try {
            return $this->queue->statsTube($queue)['total-jobs'];
        } catch (ConnectionException $ex) {
            \PHPUnit_Framework_Assert::fail("queue [$queue] not found");
        }
    }

    public function clearQueue($queue = 'default')
    {
        while ($job = $this->queue->reserveFromTube($queue, 0)) {
            $this->queue->delete($job);
        }
    }

    
    public function getQueues()
    {
        return $this->queue->listTubes();
    }

    
    public function getMessagesCurrentCountOnQueue($queue)
    {
        try {
            return $this->queue->statsTube($queue)['current-jobs-ready'];
        } catch (ConnectionException $e) {
            \PHPUnit_Framework_Assert::fail("queue [$queue] not found");
        }
    }

    public function getRequiredConfig()
    {
        return ['host'];
    }

    public function getDefaultConfig()
    {
        return ['port' => 11300, 'timeout' => 90];
    }
}
