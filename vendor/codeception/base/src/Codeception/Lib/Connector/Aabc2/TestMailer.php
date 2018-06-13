<?php
namespace Codeception\Lib\Connector\Aabc2;

use aabc\mail\BaseMailer;
use aabc\mail\BaseMessage;

class TestMailer extends BaseMailer
{
    public $messageClass = 'aabc\swiftmailer\Message';

    private $sentMessages = [];

    protected function sendMessage($message)
    {
        $this->sentMessages[] = $message;
        return true;
    }
    
    protected function saveMessage($message)
    {
        return $this->sendMessage($message);
    }

    public function getSentMessages()
    {
        return $this->sentMessages;
    }

    public function reset()
    {
        $this->sentMessages = [];
    }
}
