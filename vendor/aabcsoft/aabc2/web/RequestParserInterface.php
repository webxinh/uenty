<?php


namespace aabc\web;


interface RequestParserInterface
{
    
    public function parse($rawBody, $contentType);
}
