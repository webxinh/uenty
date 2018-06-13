<?php


namespace aabc\web;

use aabc\base\InvalidParamException;
use aabc\helpers\Json;


class JsonParser implements RequestParserInterface
{
    
    public $asArray = true;
    
    public $throwException = true;


    
    public function parse($rawBody, $contentType)
    {
        try {
            $parameters = Json::decode($rawBody, $this->asArray);
            return $parameters === null ? [] : $parameters;
        } catch (InvalidParamException $e) {
            if ($this->throwException) {
                throw new BadRequestHttpException('Invalid JSON data in request body: ' . $e->getMessage());
            }
            return [];
        }
    }
}
