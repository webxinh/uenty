<?php


namespace aabc\grid;

use Closure;
use aabc\base\InvalidConfigException;
use aabc\helpers\Html;


class RadioButtonColumn extends Column
{
    
    public $name = 'radioButtonSelection';
    
    public $radioOptions = [];


    
    public function init()
    {
        parent::init();
        if (empty($this->name)) {
            throw new InvalidConfigException('The "name" property must be set.');
        }
    }

    
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->radioOptions instanceof Closure) {
            $options = call_user_func($this->radioOptions, $model, $key, $index, $this);
        } else {
            $options = $this->radioOptions;
            if (!isset($options['value'])) {
                $options['value'] = is_array($key) ? json_encode($key, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $key;
            }
        }
        $checked = isset($options['checked']) ? $options['checked'] : false;
        return Html::radio($this->name, $checked, $options);
    }
}
