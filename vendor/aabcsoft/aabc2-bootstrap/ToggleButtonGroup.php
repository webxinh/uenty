<?php


namespace aabc\bootstrap;

use aabc\base\InvalidConfigException;


class ToggleButtonGroup extends InputWidget
{
    
    public $type;
    
    public $items = [];
    
    public $labelOptions = [];
    
    public $encodeLabels = true;


    
    public function init()
    {
        parent::init();
        $this->registerPlugin('button');
        Html::addCssClass($this->options, 'btn-group');
        $this->options['data-toggle'] = 'buttons';
    }

    
    public function run()
    {
        if (!isset($this->options['item'])) {
            $this->options['item'] = [$this, 'renderItem'];
        }
        switch ($this->type) {
            case 'checkbox':
                return Html::activeCheckboxList($this->model, $this->attribute, $this->items, $this->options);
            case 'radio':
                return Html::activeRadioList($this->model, $this->attribute, $this->items, $this->options);
            default:
                throw new InvalidConfigException("Unsupported type '{$this->type}'");
        }
    }

    
    public function renderItem($index, $label, $name, $checked, $value)
    {
        $labelOptions = $this->labelOptions;
        Html::addCssClass($labelOptions, 'btn');
        if ($checked) {
            Html::addCssClass($labelOptions, 'active');
        }
        $type = $this->type;
        if ($this->encodeLabels) {
            $label = Html::encode($label);
        }
        return Html::$type($name, $checked, ['label' => $label, 'labelOptions' => $labelOptions, 'value' => $value]);
    }
}