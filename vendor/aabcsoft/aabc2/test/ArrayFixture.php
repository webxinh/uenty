<?php


namespace aabc\test;

use Aabc;
use aabc\base\ArrayAccessTrait;
use aabc\base\InvalidConfigException;


class ArrayFixture extends Fixture implements \IteratorAggregate, \ArrayAccess, \Countable
{
    use ArrayAccessTrait;

    
    public $data = [];
    
    public $dataFile;


    
    public function load()
    {
        $this->data = $this->getData();
    }

    
    protected function getData()
    {
        if ($this->dataFile === false || $this->dataFile === null) {
            return [];
        }
        $dataFile = Aabc::getAlias($this->dataFile);
        if (is_file($dataFile)) {
            return require($dataFile);
        } else {
            throw new InvalidConfigException("Fixture data file does not exist: {$this->dataFile}");
        }
    }

    
    public function unload()
    {
        parent::unload();
        $this->data = [];
    }
}
