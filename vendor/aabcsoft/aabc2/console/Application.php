<?php


namespace aabc\console;

use Aabc;
use aabc\base\InvalidRouteException;

// define STDIN, STDOUT and STDERR if the PHP SAPI did not define them (e.g. creating console application in web env)
// http://php.net/manual/en/features.commandline.io-streams.php
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));
defined('STDERR') or define('STDERR', fopen('php://stderr', 'w'));


class Application extends \aabc\base\Application
{
    
    const OPTION_APPCONFIG = 'appconfig';

    
    public $defaultRoute = 'help';
    
    public $enableCoreCommands = true;
    
    public $controller;


    
    public function __construct($config = [])
    {
        $config = $this->loadConfig($config);
        parent::__construct($config);
    }

    
    protected function loadConfig($config)
    {
        if (!empty($_SERVER['argv'])) {
            $option = '--' . self::OPTION_APPCONFIG . '=';
            foreach ($_SERVER['argv'] as $param) {
                if (strpos($param, $option) !== false) {
                    $path = substr($param, strlen($option));
                    if (!empty($path) && is_file($file = Aabc::getAlias($path))) {
                        return require($file);
                    } else {
                        exit("The configuration file does not exist: $path\n");
                    }
                }
            }
        }

        return $config;
    }

    
    public function init()
    {
        parent::init();
        if ($this->enableCoreCommands) {
            foreach ($this->coreCommands() as $id => $command) {
                if (!isset($this->controllerMap[$id])) {
                    $this->controllerMap[$id] = $command;
                }
            }
        }
        // ensure we have the 'help' command so that we can list the available commands
        if (!isset($this->controllerMap['help'])) {
            $this->controllerMap['help'] = 'aabc\console\controllers\HelpController';
        }
    }

    
    public function handleRequest($request)
    {
        list ($route, $params) = $request->resolve();
        $this->requestedRoute = $route;
        $result = $this->runAction($route, $params);
        if ($result instanceof Response) {
            return $result;
        } else {
            $response = $this->getResponse();
            $response->exitStatus = $result;

            return $response;
        }
    }

    
    public function runAction($route, $params = [])
    {
        try {
            $res = parent::runAction($route, $params);
            return is_object($res) ? $res : (int)$res;
        } catch (InvalidRouteException $e) {
            throw new UnknownCommandException($route, $this, 0, $e);
        }
    }

    
    public function coreCommands()
    {
        return [
            'asset' => 'aabc\console\controllers\AssetController',
            'cache' => 'aabc\console\controllers\CacheController',
            'fixture' => 'aabc\console\controllers\FixtureController',
            'help' => 'aabc\console\controllers\HelpController',
            'message' => 'aabc\console\controllers\MessageController',
            'migrate' => 'aabc\console\controllers\MigrateController',
            'serve' => 'aabc\console\controllers\ServeController',
        ];
    }

    
    public function getErrorHandler()
    {
        return $this->get('errorHandler');
    }

    
    public function getRequest()
    {
        return $this->get('request');
    }

    
    public function getResponse()
    {
        return $this->get('response');
    }

    
    public function coreComponents()
    {
        return array_merge(parent::coreComponents(), [
            'request' => ['class' => 'aabc\console\Request'],
            'response' => ['class' => 'aabc\console\Response'],
            'errorHandler' => ['class' => 'aabc\console\ErrorHandler'],
        ]);
    }
}
