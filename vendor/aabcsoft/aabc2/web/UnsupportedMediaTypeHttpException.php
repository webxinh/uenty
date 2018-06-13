<?php


namespace aabc\web;


class UnsupportedMediaTypeHttpException extends HttpException
{
    
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(415, $message, $code, $previous);
    }
}
