<?php
namespace Codeception\Module;

use Codeception\Lib\Interfaces\RequiresPackage;
use Codeception\Module as CodeceptionModule;
use Codeception\Exception\ModuleException as ModuleException;
use Codeception\TestInterface;
use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;


class AMQP extends CodeceptionModule implements RequiresPackage
{
    protected $config = [
        'host'     => 'localhost',
        'username' => 'guest',
        'password' => 'guest',
        'port'     => '5672',
        'vhost'    => '/',
        'cleanup'  => true,
    ];

    
    public $connection;

    
    protected $channel;

    protected $requiredFields = ['host', 'username', 'password', 'vhost'];

    public function _requires()
    {
        return ['PhpAmqpLib\Connection\AMQPStreamConnection' => '"php-amqplib/php-amqplib": "~2.4"'];
    }

    public function _initialize()
    {
        $host = $this->config['host'];
        $port = $this->config['port'];
        $username = $this->config['username'];
        $password = $this->config['password'];
        $vhost = $this->config['vhost'];

        try {
            $this->connection = new AMQPStreamConnection($host, $port, $username, $password, $vhost);
        } catch (Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage() . ' while establishing connection to MQ server');
        }
    }

    public function _before(TestInterface $test)
    {
        if ($this->config['cleanup']) {
            $this->cleanup();
        }
    }

    
    public function pushToExchange($exchange, $message, $routing_key = null)
    {
        $message = $message instanceof AMQPMessage
            ? $message
            : new AMQPMessage($message);
        $this->connection->channel()->basic_publish($message, $exchange, $routing_key);
    }

    
    public function pushToQueue($queue, $message)
    {
        $message = $message instanceof AMQPMessage
            ? $message
            : new AMQPMessage($message);

        $this->connection->channel()->queue_declare($queue);
        $this->connection->channel()->basic_publish($message, '', $queue);
    }

    
    public function seeMessageInQueueContainsText($queue, $text)
    {
        $msg = $this->connection->channel()->basic_get($queue);
        if (!$msg) {
            $this->fail("Message was not received");
        }
        if (!$msg instanceof AMQPMessage) {
            $this->fail("Received message is not format of AMQPMessage");
        }
        $this->debugSection("Message", $msg->body);
        $this->assertContains($text, $msg->body);
    }

    
    public function grabMessageFromQueue($queue)
    {
        $message = $this->connection->channel()->basic_get($queue);
        return $message;
    }

    
    public function purgeQueue($queueName = '')
    {
        if (! in_array($queueName, $this->config['queues'])) {
            throw new ModuleException(__CLASS__, "'$queueName' doesn't exist in queues config list");
        }

        $this->connection->channel()->queue_purge($queueName, true);
    }

    
    public function purgeAllQueues()
    {
        $this->cleanup();
    }

    protected function cleanup()
    {
        if (!isset($this->config['queues'])) {
            throw new ModuleException(__CLASS__, "please set queues for cleanup");
        }
        if (!$this->connection) {
            return;
        }
        foreach ($this->config['queues'] as $queue) {
            try {
                $this->connection->channel()->queue_purge($queue);
            } catch (AMQPProtocolChannelException $e) {
                // ignore if exchange/queue doesn't exist and rethrow exception if it's something else
                if ($e->getCode() !== 404) {
                    throw $e;
                }
            }
        }
    }
}
