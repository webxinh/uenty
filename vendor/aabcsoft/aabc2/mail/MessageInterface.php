<?php


namespace aabc\mail;


interface MessageInterface
{
    
    public function getCharset();

    
    public function setCharset($charset);

    
    public function getFrom();

    
    public function setFrom($from);

    
    public function getTo();

    
    public function setTo($to);

    
    public function getReplyTo();

    
    public function setReplyTo($replyTo);

    
    public function getCc();

    
    public function setCc($cc);

    
    public function getBcc();

    
    public function setBcc($bcc);

    
    public function getSubject();

    
    public function setSubject($subject);

    
    public function setTextBody($text);

    
    public function setHtmlBody($html);

    
    public function attach($fileName, array $options = []);

    
    public function attachContent($content, array $options = []);

    
    public function embed($fileName, array $options = []);

    
    public function embedContent($content, array $options = []);

    
    public function send(MailerInterface $mailer = null);

    
    public function toString();
}
