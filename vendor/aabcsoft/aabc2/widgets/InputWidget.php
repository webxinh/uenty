<?php


namespace aabc\widgets;

use Aabc;
use aabc\base\Widget;
use aabc\base\Model;
use aabc\base\InvalidConfigException;
use aabc\helpers\Html;


class InputWidget extends Widget
{
    
    public $field;
    
    public $model;
    
    public $attribute;
    
    public $name;
    
    public $value;
    
    public $options = [];


    
    public function init()
    {
        if ($this->name === null && !$this->hasModel()) {
            throw new InvalidConfigException("Either 'name', or 'model' and 'attribute' properties must be specified.");
        }
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->getId();
        }
        parent::init();
    }

    
    protected function hasModel()
    {
        return $this->model instanceof Model && $this->attribute !== null;
    }
}
