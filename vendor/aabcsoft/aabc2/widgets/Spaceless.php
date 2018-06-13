<?php


namespace aabc\widgets;

use aabc\base\Widget;


class Spaceless extends Widget
{
    
    public function init()
    {
        ob_start();
        ob_implicit_flush(false);
    }

    
    public function run()
    {
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
    }
}
