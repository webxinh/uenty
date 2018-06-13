<?php


namespace aabc\web;


class UrlNormalizerRedirectException extends \aabc\base\Exception
{
    
    public $url;
    
    public $scheme;
    
    public $statusCode;


    
    public function __construct($url, $statusCode = 302, $scheme = false, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->url = $url;
        $this->scheme = $scheme;
        $this->statusCode = $statusCode;
        parent::__construct($message, $code, $previous);
    }
}
