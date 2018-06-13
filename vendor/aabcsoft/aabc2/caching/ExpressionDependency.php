<?php


namespace aabc\caching;


class ExpressionDependency extends Dependency
{
    
    public $expression = 'true';
    
    public $params;


    
    protected function generateDependencyData($cache)
    {
        return eval("return {$this->expression};");
    }
}
