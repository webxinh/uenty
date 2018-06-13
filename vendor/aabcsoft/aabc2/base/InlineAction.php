<?php


namespace aabc\base;

use Aabc;


class InlineAction extends Action
{
    
    public $actionMethod;


    
    public function __construct($id, $controller, $actionMethod, $config = [])
    {
        $this->actionMethod = $actionMethod;
        parent::__construct($id, $controller, $config);
    }

    
    public function runWithParams($params)
    {
        $args = $this->controller->bindActionParams($this, $params);
        Aabc::trace('Running action: ' . get_class($this->controller) . '::' . $this->actionMethod . '()', __METHOD__);
        if (Aabc::$app->requestedParams === null) {
            Aabc::$app->requestedParams = $args;
        }

        return call_user_func_array([$this->controller, $this->actionMethod], $args);
    }
}
