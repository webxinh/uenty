<?php


namespace aabc\test;

use Aabc;
use aabc\base\ArrayAccessTrait;
use aabc\base\InvalidConfigException;


abstract class BaseActiveFixture extends DbFixture implements \IteratorAggregate, \ArrayAccess, \Countable
{
    use ArrayAccessTrait;

    
    public $modelClass;
    
    public $data = [];
    
    public $dataFile;

    
    private $_models = [];


    
    public function getModel($name)
    {
        if (!isset($this->data[$name])) {
            return null;
        }
        if (array_key_exists($name, $this->_models)) {
            return $this->_models[$name];
        }

        if ($this->modelClass === null) {
            throw new InvalidConfigException('The "modelClass" property must be set.');
        }
        $row = $this->data[$name];
        /* @var $modelClass \aabc\db\ActiveRecord */
        $modelClass = $this->modelClass;
        /* @var $model \aabc\db\ActiveRecord */
        $model = new $modelClass;
        $keys = [];
        foreach ($model->primaryKey() as $key) {
            $keys[$key] = isset($row[$key]) ? $row[$key] : null;
        }

        return $this->_models[$name] = $modelClass::findOne($keys);
    }

    
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
        $this->_models = [];
    }
}
