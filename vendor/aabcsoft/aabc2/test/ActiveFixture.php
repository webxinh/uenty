<?php


namespace aabc\test;

use Aabc;
use aabc\base\InvalidConfigException;
use aabc\db\TableSchema;


class ActiveFixture extends BaseActiveFixture
{
    
    public $tableName;
    
    public $dataFile;

    
    private $_table;


    
    public function init()
    {
        parent::init();
        if ($this->modelClass === null && $this->tableName === null) {
            throw new InvalidConfigException('Either "modelClass" or "tableName" must be set.');
        }
    }

    
    public function load()
    {
        $this->resetTable();
        $this->data = [];
        $table = $this->getTableSchema();
        foreach ($this->getData() as $alias => $row) {
            $primaryKeys = $this->db->schema->insert($table->fullName, $row);
            $this->data[$alias] = array_merge($row, $primaryKeys);
        }
    }

    
    protected function getData()
    {
        if ($this->dataFile === null) {
            $class = new \ReflectionClass($this);
            $dataFile = dirname($class->getFileName()) . '/data/' . $this->getTableSchema()->fullName . '.php';

            return is_file($dataFile) ? require($dataFile) : [];
        } else {
            return parent::getData();
        }
    }

    
    protected function resetTable()
    {
        $table = $this->getTableSchema();
        $this->db->createCommand()->delete($table->fullName)->execute();
        if ($table->sequenceName !== null) {
            $this->db->createCommand()->resetSequence($table->fullName, 1)->execute();
        }
    }

    
    public function getTableSchema()
    {
        if ($this->_table !== null) {
            return $this->_table;
        }

        $db = $this->db;
        $tableName = $this->tableName;
        if ($tableName === null) {
            /* @var $modelClass \aabc\db\ActiveRecord */
            $modelClass = $this->modelClass;
            $tableName = $modelClass::tableName();
        }

        $this->_table = $db->getSchema()->getTableSchema($tableName);
        if ($this->_table === null) {
            throw new InvalidConfigException("Table does not exist: {$tableName}");
        }

        return $this->_table;
    }
}
