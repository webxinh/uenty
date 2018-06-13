<?php


namespace aabc\db;


interface ActiveQueryInterface extends QueryInterface
{
    
    public function asArray($value = true);

    
    public function one($db = null);

    
    public function indexBy($column);

    
    public function with();

    
    public function via($relationName, callable $callable = null);

    
    public function findFor($name, $model);
}
