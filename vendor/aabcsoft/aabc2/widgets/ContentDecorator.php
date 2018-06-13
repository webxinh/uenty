<?php


namespace aabc\widgets;

use aabc\base\InvalidConfigException;
use aabc\base\Widget;


class ContentDecorator extends Widget
{
    
    public $viewFile;
    
    public $params = [];


    
    public function init()
    {
        if ($this->viewFile === null) {
            throw new InvalidConfigException('ContentDecorator::viewFile must be set.');
        }
        ob_start();
        ob_implicit_flush(false);
    }

    
    public function run()
    {
        $params = $this->params;
        $params['content'] = ob_get_clean();
        // render under the existing context
        echo $this->view->renderFile($this->viewFile, $params);
    }
}
