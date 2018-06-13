<?php


namespace aabc\base;


class ViewEvent extends Event
{
    
    public $viewFile;
    
    public $params;
    
    public $output;
    
    public $isValid = true;
}
