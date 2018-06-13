<?php


namespace aabc\mail;


interface MailerInterface
{
    
    public function compose($view = null, array $params = []);

    
    public function send($message);

    
    public function sendMultiple(array $messages);
}
