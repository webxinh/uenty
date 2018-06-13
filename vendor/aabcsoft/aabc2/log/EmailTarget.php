<?php


namespace aabc\log;

use Aabc;
use aabc\base\InvalidConfigException;
use aabc\di\Instance;
use aabc\mail\MailerInterface;


class EmailTarget extends Target
{
    
    public $message = [];
    
    public $mailer = 'mailer';


    
    public function init()
    {
        parent::init();
        if (empty($this->message['to'])) {
            throw new InvalidConfigException('The "to" option must be set for EmailTarget::message.');
        }
        $this->mailer = Instance::ensure($this->mailer, 'aabc\mail\MailerInterface');
    }

    
    public function export()
    {
        // moved initialization of subject here because of the following issue
        // https://github.com/aabcsoft/aabc2/issues/1446
        if (empty($this->message['subject'])) {
            $this->message['subject'] = 'Application Log';
        }
        $messages = array_map([$this, 'formatMessage'], $this->messages);
        $body = wordwrap(implode("\n", $messages), 70);
        $this->composeMessage($body)->send($this->mailer);
    }

    
    protected function composeMessage($body)
    {
        $message = $this->mailer->compose();
        Aabc::configure($message, $this->message);
        $message->setTextBody($body);

        return $message;
    }
}
