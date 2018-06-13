<?php


namespace aabc\web;


class RangeNotSatisfiableHttpException extends HttpException
{
    
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(416, $message, $code, $previous);
    }
}
