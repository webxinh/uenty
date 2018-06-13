<?php
namespace Codeception\Lib\Driver;

use Codeception\Lib\Interfaces\Queue;

class Iron implements Queue
{
    
    protected $queue;

    
    public function openConnection($config)
    {
        $this->queue = new \IronMQ([
            "token"      => $config['token'],
            "project_id" => $config['project'],
            "host"       => $config['host']
        ]);
        if (!$this->queue) {
            \PHPUnit_Framework_Assert::fail('connection failed or timed-out.');
        }
    }

    
    public function addMessageToQueue($message, $queue)
    {
        $this->queue->postMessage($queue, $message);
    }

    
    public function getQueues()
    {
        // Format the output to suit
        $queues = [];
        foreach ($this->queue->getQueues() as $queue) {
            $queues[] = $queue->name;
        }
        return $queues;
    }

    
    public function getMessagesCurrentCountOnQueue($queue)
    {
        try {
            return $this->queue->getQueue($queue)->size;
        } catch (\Http_Exception $ex) {
            \PHPUnit_Framework_Assert::fail("queue [$queue] not found");
        }
    }

    
    public function getMessagesTotalCountOnQueue($queue)
    {
        try {
            return $this->queue->getQueue($queue)->total_messages;
        } catch (\Http_Exception $e) {
            \PHPUnit_Framework_Assert::fail("queue [$queue] not found");
        }
    }

    public function clearQueue($queue)
    {
        try {
            $this->queue->clearQueue($queue);
        } catch (\Http_Exception $ex) {
            \PHPUnit_Framework_Assert::fail("queue [$queue] not found");
        }
    }

    public function getRequiredConfig()
    {
        return ['host', 'token', 'project'];
    }

    public function getDefaultConfig()
    {
        return [];
    }
}
