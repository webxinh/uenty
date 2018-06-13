<?php


namespace aabc\mail;

use aabc\base\ErrorHandler;
use aabc\base\Object;
use Aabc;


abstract class BaseMessage extends Object implements MessageInterface
{
    
    public $mailer;


    
    public function send(MailerInterface $mailer = null)
    {
        if ($mailer === null && $this->mailer === null) {
            $mailer = Aabc::$app->getMailer();
        } elseif ($mailer === null) {
            $mailer = $this->mailer;
        }
        return $mailer->send($this);
    }

    
    public function __toString()
    {
        // __toString cannot throw exception
        // use trigger_error to bypass this limitation
        try {
            return $this->toString();
        } catch (\Exception $e) {
            ErrorHandler::convertExceptionToError($e);
            return '';
        }
    }
}
