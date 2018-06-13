<?php
namespace Codeception\Module;

use Codeception\Lib\Framework;
use Codeception\TestInterface;
use Codeception\Configuration;
use Codeception\Lib\Interfaces\DoctrineProvider;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Util\ReflectionHelper;
use Zend\Console\Console;
use Zend\EventManager\StaticEventManager;
use Codeception\Lib\Connector\ZF2 as ZF2Connector;


class ZF2 extends Framework implements DoctrineProvider, PartedModule
{
    protected $config = [
        'config' => 'tests/application.config.php',
    ];

    
    public $application;

    
    public $db;

    
    public $client;

    protected $applicationConfig;

    protected $queries = 0;
    protected $time = 0;

    
    private $domainCollector = [];

    public function _initialize()
    {
        $initAutoloaderFile = Configuration::projectDir() . 'init_autoloader.php';
        if (file_exists($initAutoloaderFile)) {
            require $initAutoloaderFile;
        }

        $this->applicationConfig = require Configuration::projectDir() . $this->config['config'];
        if (isset($this->applicationConfig['module_listener_options']['config_cache_enabled'])) {
            $this->applicationConfig['module_listener_options']['config_cache_enabled'] = false;
        }
        Console::overrideIsConsole(false);

        //grabServiceFromContainer may need client in beforeClass hooks of modules or helpers
        $this->client = new ZF2Connector();
        $this->client->setApplicationConfig($this->applicationConfig);
    }

    public function _before(TestInterface $test)
    {
        $this->client = new ZF2Connector();
        $this->client->setApplicationConfig($this->applicationConfig);
        $_SERVER['REQUEST_URI'] = '';
    }

    public function _after(TestInterface $test)
    {
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];

        if (class_exists('Zend\EventManager\StaticEventManager')) {
            // reset singleton (ZF2)
            StaticEventManager::resetInstance();
        }

        //Close the session, if any are open
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        $this->queries = 0;
        $this->time = 0;

        parent::_after($test);
    }

    public function _afterSuite()
    {
        unset($this->client);
    }

    public function _getEntityManager()
    {
        if (!$this->client) {
            $this->client = new ZF2Connector();
            $this->client->setApplicationConfig($this->applicationConfig);
        }

        return $this->grabServiceFromContainer('Doctrine\ORM\EntityManager');
    }

    
    public function grabServiceFromContainer($service)
    {
        return $this->client->grabServiceFromContainer($service);
    }

    
    public function addServiceToContainer($name, $service)
    {
        $this->client->addServiceToContainer($name, $service);
    }

    
    public function amOnRoute($routeName, array $params = [])
    {
        $router = $this->client->grabServiceFromContainer('router');
        $url = $router->assemble($params, ['name' => $routeName]);
        $this->amOnPage($url);
    }

    
    public function seeCurrentRouteIs($routeName, array $params = [])
    {
        $router = $this->client->grabServiceFromContainer('router');
        $url = $router->assemble($params, ['name' => $routeName]);
        $this->seeCurrentUrlEquals($url);
    }

    protected function getInternalDomains()
    {
        
        $router = $this->client->grabServiceFromContainer('router');
        $this->domainCollector = [];
        $this->addInternalDomainsFromRoutes($router->getRoutes());
        return array_unique($this->domainCollector);
    }

    private function addInternalDomainsFromRoutes($routes)
    {
        foreach ($routes as $name => $route) {
            if ($route instanceof \Zend\Mvc\Router\Http\Hostname || $route instanceof \Zend\Router\Http\Hostname) {
                $this->addInternalDomain($route);
            } elseif ($route instanceof \Zend\Mvc\Router\Http\Part || $route instanceof \Zend\Router\Http\Part) {
                $parentRoute = ReflectionHelper::readPrivateProperty($route, 'route');
                if ($parentRoute instanceof \Zend\Mvc\Router\Http\Hostname || $parentRoute instanceof \Zend\Mvc\Router\Http\Hostname) {
                    $this->addInternalDomain($parentRoute);
                }
                // this is necessary to instantiate child routes
                try {
                    $route->assemble([], []);
                } catch (\Exception $e) {
                }
                $this->addInternalDomainsFromRoutes($route->getRoutes());
            }
        }
    }

    private function addInternalDomain($route)
    {
        $regex = ReflectionHelper::readPrivateProperty($route, 'regex');
        $this->domainCollector []= '/^' . $regex . '$/';
    }

    public function _parts()
    {
        return ['services'];
    }
}
