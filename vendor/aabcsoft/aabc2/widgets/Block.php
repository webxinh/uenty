<?php


namespace aabc\widgets;

use aabc\base\Widget;


class Block extends Widget
{
    
    public $renderInPlace = false;


    
    public function init()
    {
        ob_start();
        ob_implicit_flush(false);
    }

    
    public function run()
    {
        $block = ob_get_clean();
        if ($this->renderInPlace) {
            echo $block;
        }
        $this->view->blocks[$this->getId()] = $block;
    }
}
