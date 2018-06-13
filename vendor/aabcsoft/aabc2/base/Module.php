<?php


namespace aabc\base;

use Aabc;
use aabc\di\ServiceLocator;


class Module extends ServiceLocator
{
    
    const EVENT_BEFORE_ACTION = 'beforeAction';
    
    const EVENT_AFTER_ACTION = 'afterAction';

    
    public $params = [];
    
    public $id;
    
    public $module;
    
    public $layout;
    
    public $controllerMap = [];
    
    public $controllerNamespace;
    
    public $defaultRoute = 'default';

    
    private $_basePath;
    
    private $_viewPath;
    
    private $_layoutPath;
    
    private $_modules = [];
    
    private $_version;


    
    public function __construct($id, $parent = null, $config = [])
    {
        $this->id = $id;
        $this->module = $parent;
        parent::__construct($config);
    }

    
    public static function getInstance()
    {
        $class = get_called_class();
        return isset(Aabc::$app->loadedModules[$class]) ? Aabc::$app->loadedModules[$class] : null;
    }

    
    public static function setInstance($instance)
    {
        if ($instance === null) {
            unset(Aabc::$app->loadedModules[get_called_class()]);
        } else {
            Aabc::$app->loadedModules[get_class($instance)] = $instance;
        }
    }

    
    public function init()
    {
        if ($this->controllerNamespace === null) {
            $class = get_class($this);
            if (($pos = strrpos($class, '\\')) !== false) {
                $this->controllerNamespace = substr($class, 0, $pos) . '\\controllers';
            }
        }
    }

    
    public function getUniqueId()
    {
        return $this->module ? ltrim($this->module->getUniqueId() . '/' . $this->id, '/') : $this->id;
    }

    
    public function getBasePath()
    {
        if ($this->_basePath === null) {
            $class = new \ReflectionClass($this);
            $this->_basePath = dirname($class->getFileName());
        }

        return $this->_basePath;
    }

    
    public function setBasePath($path)
    {
        $path = Aabc::getAlias($path);
        $p = strncmp($path, 'phar://', 7) === 0 ? $path : realpath($path);
        if ($p !== false && is_dir($p)) {
            $this->_basePath = $p;
        } else {
            throw new InvalidParamException("The directory does not exist: $path");
        }
    }

    
    public function getControllerPath()
    {
        return Aabc::getAlias('@' . str_replace('\\', '/', $this->controllerNamespace));
    }

    
    public function getViewPath()
    {
        if ($this->_viewPath === null) {
            $this->_viewPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'views';
        }
        return $this->_viewPath;
    }

    
    public function setViewPath($path)
    {
        $this->_viewPath = Aabc::getAlias($path);
    }

    
    public function getLayoutPath()
    {
        if ($this->_layoutPath === null) {
            $this->_layoutPath = $this->getViewPath() . DIRECTORY_SEPARATOR . 'layouts';
        }

        return $this->_layoutPath;
    }

    
    public function setLayoutPath($path)
    {
        $this->_layoutPath = Aabc::getAlias($path);
    }

    
    public function getVersion()
    {
        if ($this->_version === null) {
            $this->_version = $this->defaultVersion();
        } else {
            if (!is_scalar($this->_version)) {
                $this->_version = call_user_func($this->_version, $this);
            }
        }
        return $this->_version;
    }

    
    public function setVersion($version)
    {
        $this->_version = $version;
    }

    
    protected function defaultVersion()
    {
        if ($this->module === null) {
            return '1.0';
        }
        return $this->module->getVersion();
    }

    
    public function setAliases($aliases)
    {
        foreach ($aliases as $name => $alias) {
            Aabc::setAlias($name, $alias);
        }
    }

    
    public function hasModule($id)
    {
        if (($pos = strpos($id, '/')) !== false) {
            // sub-module
            $module = $this->getModule(substr($id, 0, $pos));

            return $module === null ? false : $module->hasModule(substr($id, $pos + 1));
        }
        return isset($this->_modules[$id]);
    }

    
    public function getModule($id, $load = true)
    {
        if (($pos = strpos($id, '/')) !== false) {
            // sub-module
            $module = $this->getModule(substr($id, 0, $pos));

            return $module === null ? null : $module->getModule(substr($id, $pos + 1), $load);
        }

        if (isset($this->_modules[$id])) {
            if ($this->_modules[$id] instanceof Module) {
                return $this->_modules[$id];
            } elseif ($load) {
                Aabc::trace("Loading module: $id", __METHOD__);
                /* @var $module Module */
                $module = Aabc::createObject($this->_modules[$id], [$id, $this]);
                $module->setInstance($module);
                return $this->_modules[$id] = $module;
            }
        }

        return null;
    }

    
    public function setModule($id, $module)
    {
        if ($module === null) {
            unset($this->_modules[$id]);
        } else {
            $this->_modules[$id] = $module;
        }
    }

    
    public function getModules($loadedOnly = false)
    {
        if ($loadedOnly) {
            $modules = [];
            foreach ($this->_modules as $module) {
                if ($module instanceof Module) {
                    $modules[] = $module;
                }
            }

            return $modules;
        }
        return $this->_modules;
    }

    
    public function setModules($modules)
    {
        foreach ($modules as $id => $module) {
            $this->_modules[$id] = $module;
        }
    }

    
    public function runAction($route, $params = [])
    {
        $parts = $this->createController($route);
        if (is_array($parts)) {
            /* @var $controller Controller */
            list($controller, $actionID) = $parts;
            $oldController = Aabc::$app->controller;
            Aabc::$app->controller = $controller;
            $result = $controller->runAction($actionID, $params);
            if ($oldController !== null) {
                Aabc::$app->controller = $oldController;
            }

            return $result;
        }

        $id = $this->getUniqueId();
        throw new InvalidRouteException('Unable to resolve the request "' . ($id === '' ? $route : $id . '/' . $route) . '".');
    }

    
    public function createController($route)
    {
        if ($route === '') {
            $route = $this->defaultRoute;
        }

        // double slashes or leading/ending slashes may cause substr problem
        $route = trim($route, '/');
        if (strpos($route, '//') !== false) {
            return false;
        }

        if (strpos($route, '/') !== false) {
            list ($id, $route) = explode('/', $route, 2);
        } else {
            $id = $route;
            $route = '';
        }

        // module and controller map take precedence
        if (isset($this->controllerMap[$id])) {
            $controller = Aabc::createObject($this->controllerMap[$id], [$id, $this]);
            return [$controller, $route];
        }
        $module = $this->getModule($id);
        if ($module !== null) {
            return $module->createController($route);
        }

        if (($pos = strrpos($route, '/')) !== false) {
            $id .= '/' . substr($route, 0, $pos);
            $route = substr($route, $pos + 1);
        }

        $controller = $this->createControllerByID($id);
        if ($controller === null && $route !== '') {
            $controller = $this->createControllerByID($id . '/' . $route);
            $route = '';
        }

        return $controller === null ? false : [$controller, $route];
    }

    
    public function createControllerByID($id)
    {
        $pos = strrpos($id, '/');
        if ($pos === false) {
            $prefix = '';
            $className = $id;
        } else {
            $prefix = substr($id, 0, $pos + 1);
            $className = substr($id, $pos + 1);
        }

        if (!preg_match('%^[a-z][a-z0-9\\-_]*$%', $className)) {
            return null;
        }
        if ($prefix !== '' && !preg_match('%^[a-z0-9_/]+$%i', $prefix)) {
            return null;
        }

        $className = str_replace(' ', '', ucwords(str_replace('-', ' ', $className))) . 'Controller';
        $className = ltrim($this->controllerNamespace . '\\' . str_replace('/', '\\', $prefix)  . $className, '\\');
        if (strpos($className, '-') !== false || !class_exists($className)) {
            return null;
        }

        if (is_subclass_of($className, 'aabc\base\Controller')) {
            $controller = Aabc::createObject($className, [$id, $this]);
            return get_class($controller) === $className ? $controller : null;
        } elseif (AABC_DEBUG) {
            throw new InvalidConfigException("Controller class must extend from \\aabc\\base\\Controller.");
        }
        return null;
    }

    
    public function beforeAction($action)
    {
        $event = new ActionEvent($action);
        $this->trigger(self::EVENT_BEFORE_ACTION, $event);
        return $event->isValid;
    }

    
    public function afterAction($action, $result)
    {
        $event = new ActionEvent($action);
        $event->result = $result;
        $this->trigger(self::EVENT_AFTER_ACTION, $event);
        return $event->result;
    }
}
