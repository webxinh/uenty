<?php


namespace aabc\web;


class UnprocessableEntityHttpException extends HttpException
{
    
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(422, $message, $code, $previous);
    }
}
