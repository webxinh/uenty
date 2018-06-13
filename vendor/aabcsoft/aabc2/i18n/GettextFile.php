<?php


namespace aabc\i18n;

use aabc\base\Component;


abstract class GettextFile extends Component
{
    
    abstract public function load($filePath, $context);

    
    abstract public function save($filePath, $messages);
}
