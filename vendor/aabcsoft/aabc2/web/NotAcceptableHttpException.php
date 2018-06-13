<?php


namespace aabc\web;


class NotAcceptableHttpException extends HttpException
{
    
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(406, $message, $code, $previous);
    }
}
