<?php


namespace aabc\base;


abstract class ViewRenderer extends Component
{
    
    abstract public function render($view, $file, $params);
}
