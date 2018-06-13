<?php


namespace aabc\base;

use Aabc;


class Action extends Component
{
    
    public $id;
    
    public $controller;


    
    public function __construct($id, $controller, $config = [])
    {
        $this->id = $id;
        $this->controller = $controller;
        parent::__construct($config);
    }

    
    public function getUniqueId()
    {
        return $this->controller->getUniqueId() . '/' . $this->id;
    }

    
    public function runWithParams($params)
    {
        if (!method_exists($this, 'run')) {
            throw new InvalidConfigException(get_class($this) . ' must define a "run()" method.');
        }
        $args = $this->controller->bindActionParams($this, $params);
        Aabc::trace('Running action: ' . get_class($this) . '::run()', __METHOD__);
        if (Aabc::$app->requestedParams === null) {
            Aabc::$app->requestedParams = $args;
        }
        if ($this->beforeRun()) {
            $result = call_user_func_array([$this, 'run'], $args);
            $this->afterRun();

            return $result;
        } else {
            return null;
        }
    }

    
    protected function beforeRun()
    {
        return true;
    }

    
    protected function afterRun()
    {
    }
}
