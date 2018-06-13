<?php


namespace aabc\rbac;

use aabc\base\Object;


class Item extends Object
{
    const TYPE_ROLE = 1;
    const TYPE_PERMISSION = 2;

    
    public $type;
    
    public $name;
    
    public $description;
    
    public $ruleName;
    
    public $data;
    
    public $createdAt;
    
    public $updatedAt;
}
