<?php
namespace Codeception\Lib\Interfaces;

interface Queue
{

    
    public function openConnection($config);

    
    public function addMessageToQueue($message, $queue);

    
    public function getQueues();

    
    public function getMessagesCurrentCountOnQueue($queue);

    
    public function getMessagesTotalCountOnQueue($queue);

    public function clearQueue($queue);

    public function getRequiredConfig();

    public function getDefaultConfig();
}
