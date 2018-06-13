<?php


namespace aabc\db;

use aabc\base\Object;


class BatchQueryResult extends Object implements \Iterator
{
    
    public $db;
    
    public $query;
    
    public $batchSize = 100;
    
    public $each = false;

    
    private $_dataReader;
    
    private $_batch;
    
    private $_value;
    
    private $_key;


    
    public function __destruct()
    {
        // make sure cursor is closed
        $this->reset();
    }

    
    public function reset()
    {
        if ($this->_dataReader !== null) {
            $this->_dataReader->close();
        }
        $this->_dataReader = null;
        $this->_batch = null;
        $this->_value = null;
        $this->_key = null;
    }

    
    public function rewind()
    {
        $this->reset();
        $this->next();
    }

    
    public function next()
    {
        if ($this->_batch === null || !$this->each || $this->each && next($this->_batch) === false) {
            $this->_batch = $this->fetchData();
            reset($this->_batch);
        }

        if ($this->each) {
            $this->_value = current($this->_batch);
            if ($this->query->indexBy !== null) {
                $this->_key = key($this->_batch);
            } elseif (key($this->_batch) !== null) {
                $this->_key++;
            } else {
                $this->_key = null;
            }
        } else {
            $this->_value = $this->_batch;
            $this->_key = $this->_key === null ? 0 : $this->_key + 1;
        }
    }

    
    protected function fetchData()
    {
        if ($this->_dataReader === null) {
            $this->_dataReader = $this->query->createCommand($this->db)->query();
        }

        $rows = [];
        $count = 0;
        while ($count++ < $this->batchSize && ($row = $this->_dataReader->read())) {
            $rows[] = $row;
        }

        return $this->query->populate($rows);
    }

    
    public function key()
    {
        return $this->_key;
    }

    
    public function current()
    {
        return $this->_value;
    }

    
    public function valid()
    {
        return !empty($this->_batch);
    }
}
