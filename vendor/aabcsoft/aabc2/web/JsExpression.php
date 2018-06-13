<?php


namespace aabc\web;

use aabc\base\Object;


class JsExpression extends Object
{
    
    public $expression;


    
    public function __construct($expression, $config = [])
    {
        $this->expression = $expression;
        parent::__construct($config);
    }

    
    public function __toString()
    {
        return $this->expression;
    }
}
