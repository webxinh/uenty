<?php


namespace aabc\db;

use aabc\base\Object;
use aabc\base\InvalidParamException;


class TableSchema extends Object
{
    
    public $schemaName;
    
    public $name;
    
    public $fullName;
    
    public $primaryKey = [];
    
    public $sequenceName;
    
    public $foreignKeys = [];
    
    public $columns = [];


    
    public function getColumn($name)
    {
        return isset($this->columns[$name]) ? $this->columns[$name] : null;
    }

    
    public function getColumnNames()
    {
        return array_keys($this->columns);
    }

    
    public function fixPrimaryKey($keys)
    {
        $keys = (array) $keys;
        $this->primaryKey = $keys;
        foreach ($this->columns as $column) {
            $column->isPrimaryKey = false;
        }
        foreach ($keys as $key) {
            if (isset($this->columns[$key])) {
                $this->columns[$key]->isPrimaryKey = true;
            } else {
                throw new InvalidParamException("Primary key '$key' cannot be found in table '{$this->name}'.");
            }
        }
    }
}
