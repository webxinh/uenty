<?php


namespace aabc\web;


class BadRequestHttpException extends HttpException
{
    
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(400, $message, $code, $previous);
    }
}
