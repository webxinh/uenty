<?php
namespace Codeception\Lib\Driver;

use Codeception\Exception\TestRuntime;
use Codeception\Lib\Interfaces\Queue;
use Aws\Sqs\SqsClient;
use Aws\Common\Credentials\Credentials;

class AmazonSQS implements Queue
{
    protected $queue;

    
    public function openConnection($config)
    {
        $params = [
            'region' => $config['region']
        ];

        if (! empty($config['key']) && ! empty($config['secret'])) {
            $params['credentials'] = new Credentials($config['key'], $config['secret']);
        }

        if (! empty($config['profile'])) {
            $params['profile'] = $config['profile'];
        }

        $this->queue = SqsClient::factory($params);
        if (!$this->queue) {
            throw new TestRuntime('connection failed or timed-out.');
        }
    }

    
    public function addMessageToQueue($message, $queue)
    {
        $this->queue->sendMessage([
            'QueueUrl' => $this->getQueueURL($queue),
            'MessageBody' => $message,
        ]);
    }

    
    public function getQueues()
    {
        $queueNames = [];
        $queues = $this->queue->listQueues(['QueueNamePrefix' => ''])->get('QueueUrls');
        foreach ($queues as $queue) {
            $tokens = explode('/', $queue);
            $queueNames[] = $tokens[sizeof($tokens) - 1];
        }
        return $queueNames;
    }

    
    public function getMessagesCurrentCountOnQueue($queue)
    {
        return $this->queue->getQueueAttributes([
            'QueueUrl' => $this->getQueueURL($queue),
            'AttributeNames' => ['ApproximateNumberOfMessages'],
        ])->get('Attributes')['ApproximateNumberOfMessages'];
    }

    
    public function getMessagesTotalCountOnQueue($queue)
    {
        return $this->queue->getQueueAttributes([
            'QueueUrl' => $this->getQueueURL($queue),
            'AttributeNames' => ['ApproximateNumberOfMessages'],
        ])->get('Attributes')['ApproximateNumberOfMessages'];
    }

    public function clearQueue($queue)
    {
        $queueURL = $this->getQueueURL($queue);
        while (true) {
            $res = $this->queue->receiveMessage(['QueueUrl' => $queueURL]);

            if (!$res->getPath('Messages')) {
                return;
            }
            foreach ($res->getPath('Messages') as $msg) {
                $this->queue->deleteMessage([
                    'QueueUrl' => $queueURL,
                    'ReceiptHandle' => $msg['ReceiptHandle']
                ]);
            }
        }
    }

    
    private function getQueueURL($queue)
    {
        $queues = $this->queue->listQueues(['QueueNamePrefix' => ''])->get('QueueUrls');
        foreach ($queues as $queueURL) {
            $tokens = explode('/', $queueURL);
            if (strtolower($queue) == strtolower($tokens[sizeof($tokens) - 1])) {
                return $queueURL;
            }
        }
        throw new TestRuntime('queue [' . $queue . '] not found');
    }

    public function getRequiredConfig()
    {
        return ['region'];
    }

    public function getDefaultConfig()
    {
        return [];
    }
}
