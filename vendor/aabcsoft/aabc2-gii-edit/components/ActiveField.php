<?php


namespace aabc\gii\components;

use aabc\gii\Generator;
use aabc\helpers\Json;


class ActiveField extends \aabc\widgets\ActiveField
{
    
    public $model;


    
    public function init()
    {
        $stickyAttributes = $this->model->stickyAttributes();
        if (in_array($this->attribute, $stickyAttributes)) {
            $this->sticky();
        }
        $hints = $this->model->hints();
        if (isset($hints[$this->attribute])) {
            $this->hint($hints[$this->attribute]);
        }
        $autoCompleteData = $this->model->autoCompleteData();
        if (isset($autoCompleteData[$this->attribute])) {
            if (is_callable($autoCompleteData[$this->attribute])) {
                $this->autoComplete(call_user_func($autoCompleteData[$this->attribute]));
            } else {
                $this->autoComplete($autoCompleteData[$this->attribute]);
            }
        }
    }

    
    public function sticky()
    {
        $this->options['class'] .= ' sticky';

        return $this;
    }

    
    public function autoComplete($data)
    {
        static $counter = 0;
        $this->inputOptions['class'] .= ' typeahead typeahead-' . (++$counter);
        foreach ($data as &$item) {
            $item = ['word' => $item];
        }
        $this->form->getView()->registerJs("aabc.gii.autocomplete($counter, " . Json::htmlEncode($data) . ");");

        return $this;
    }
}
