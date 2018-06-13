<?php


namespace aabc\rbac;

use aabc\base\Object;


abstract class Rule extends Object
{
    
    public $name;
    
    public $createdAt;
    
    public $updatedAt;


    
    abstract public function execute($user, $item, $params);
}
