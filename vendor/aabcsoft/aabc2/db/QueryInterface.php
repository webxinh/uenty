<?php


namespace aabc\db;


interface QueryInterface
{
    
    public function all($db = null);

    
    public function one($db = null);

    
    public function count($q = '*', $db = null);

    
    public function exists($db = null);

    
    public function indexBy($column);

    
    public function where($condition);

    
    public function andWhere($condition);

    
    public function orWhere($condition);

    
    public function filterWhere(array $condition);

    
    public function andFilterWhere(array $condition);

    
    public function orFilterWhere(array $condition);

    
    public function orderBy($columns);

    
    public function addOrderBy($columns);

    
    public function limit($limit);

    
    public function offset($offset);

    
    public function emulateExecution($value = true);
}
