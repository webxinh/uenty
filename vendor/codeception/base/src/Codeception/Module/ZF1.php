<?php
namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Lib\Framework;
use Codeception\TestInterface;
use Codeception\Exception\ModuleException;
use Codeception\Util\ReflectionHelper;
use Codeception\Lib\Connector\ZF1 as ZF1Connector;
use Zend_Controller_Router_Route_Hostname;
use Zend_Controller_Router_Route_Chain;


class ZF1 extends Framework
{
    protected $config = [
        'env' => 'testing',
        'config' => 'application/configs/application.ini',
        'app_path' => 'application',
        'lib_path' => 'library'
    ];

    
    public $bootstrap;

    
    public $db;

    
    public $client;

    protected $queries = 0;
    protected $time = 0;


    
    private $domainCollector = [];

    public function _initialize()
    {
        defined('APPLICATION_ENV') || define('APPLICATION_ENV', $this->config['env']);
        defined('APPLICATION_PATH') || define(
            'APPLICATION_PATH',
            Configuration::projectDir() . $this->config['app_path']
        );
        defined('LIBRARY_PATH') || define('LIBRARY_PATH', Configuration::projectDir() . $this->config['lib_path']);

        // Ensure library/ is on include_path
        set_include_path(
            implode(
                PATH_SEPARATOR,
                [
                    LIBRARY_PATH,
                    get_include_path(),
                ]
            )
        );

        require_once 'Zend/Loader/Autoloader.php';
        \Zend_Loader_Autoloader::getInstance();
    }

    public function _before(TestInterface $test)
    {
        $this->client = new ZF1Connector();

        \Zend_Session::$_unitTestEnabled = true;
        try {
            $this->bootstrap = new \Zend_Application(
                $this->config['env'],
                Configuration::projectDir() . $this->config['config']
            );
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
        $this->bootstrap->bootstrap();
        $this->client->setBootstrap($this->bootstrap);

        $db = $this->bootstrap->getBootstrap()->getResource('db');
        if ($db instanceof \Zend_Db_Adapter_Abstract) {
            $this->db = $db;
            $this->db->getProfiler()->setEnabled(true);
            $this->db->getProfiler()->clear();
        }
    }

    public function _after(TestInterface $test)
    {
        $_SESSION = [];
        $_GET = [];
        $_POST = [];
        $_COOKIE = [];
        if ($this->bootstrap) {
            $fc = $this->bootstrap->getBootstrap()->getResource('frontcontroller');
            if ($fc) {
                $fc->resetInstance();
            }
        }
        \Zend_Layout::resetMvcInstance();
        \Zend_Controller_Action_HelperBroker::resetHelpers();
        \Zend_Session::$_unitTestEnabled = true;
        \Zend_Registry::_unsetInstance();
        $this->queries = 0;
        $this->time = 0;

        parent::_after($test);
    }

    
    protected function debugResponse($url)
    {
        parent::debugResponse($url);

        $this->debugSection('Session', json_encode($_COOKIE));
        if ($this->db) {
            $profiler = $this->db->getProfiler();
            $queries = $profiler->getTotalNumQueries() - $this->queries;
            $time = $profiler->getTotalElapsedSecs() - $this->time;
            $this->debugSection('Db', $queries . ' queries');
            $this->debugSection('Time', round($time, 2) . ' secs taken');
            $this->time = $profiler->getTotalElapsedSecs();
            $this->queries = $profiler->getTotalNumQueries();
        }
    }

    
    public function amOnRoute($routeName, array $params = [])
    {
        $router = $this->bootstrap->getBootstrap()->getResource('frontcontroller')->getRouter();
        $url = $router->assemble($params, $routeName);
        $this->amOnPage($url);
    }

    
    public function seeCurrentRouteIs($routeName, array $params = [])
    {
        $router = $this->bootstrap->getBootstrap()->getResource('frontcontroller')->getRouter();
        $url = $router->assemble($params, $routeName);
        $this->seeCurrentUrlEquals($url);
    }

    protected function getInternalDomains()
    {
        $router = $this->bootstrap->getBootstrap()->getResource('frontcontroller')->getRouter();
        $this->domainCollector = [];
        $this->addInternalDomainsFromRoutes($router->getRoutes());
        return array_unique($this->domainCollector);
    }

    private function addInternalDomainsFromRoutes($routes)
    {
        foreach ($routes as $name => $route) {
            try {
                $route->assemble([]);
            } catch (\Exception $e) {
            }
            if ($route instanceof Zend_Controller_Router_Route_Hostname) {
                $this->addInternalDomain($route);
            } elseif ($route instanceof Zend_Controller_Router_Route_Chain) {
                $chainRoutes = ReflectionHelper::readPrivateProperty($route, '_routes');
                $this->addInternalDomainsFromRoutes($chainRoutes);
            }
        }
    }

    private function addInternalDomain(Zend_Controller_Router_Route_Hostname $route)
    {
        $parts = ReflectionHelper::readPrivateProperty($route, '_parts');
        foreach ($parts as &$part) {
            if ($part === null) {
                $part = '[^.]+';
            }
        }
        $regex = implode('\.', $parts);
        $this->domainCollector []= '/^' . $regex . '$/iu';
    }
}
