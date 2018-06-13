<?php


namespace aabc\bootstrap;

use Aabc;
use aabc\base\InvalidConfigException;


class ActiveForm extends \aabc\widgets\ActiveForm
{
    
    public $fieldClass = 'aabc\bootstrap\ActiveField';
    
    public $options = ['role' => 'form'];
    
    public $layout = 'default';


    
    public function init()
    {
        if (!in_array($this->layout, ['default', 'horizontal', 'inline'])) {
            throw new InvalidConfigException('Invalid layout type: ' . $this->layout);
        }

        if ($this->layout !== 'default') {
            Html::addCssClass($this->options, 'form-' . $this->layout);
        }
        parent::init();
    }

    
    public function field($model, $attribute, $options = [])
    {
        return parent::field($model, $attribute, $options);
    }
}
