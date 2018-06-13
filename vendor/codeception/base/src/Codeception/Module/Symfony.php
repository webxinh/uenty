<?php
namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Lib\Framework;
use Codeception\Exception\ModuleRequireException;
use Codeception\Lib\Connector\Symfony as SymfonyConnector;
use Codeception\Lib\Interfaces\DoctrineProvider;
use Codeception\Lib\Interfaces\PartedModule;
use Symfony\Component\Finder\Finder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\VarDumper\Cloner\Data;


class Symfony extends Framework implements DoctrineProvider, PartedModule
{
    
    public $kernel;

    public $config = [
        'app_path' => 'app',
        'var_path' => 'app',
        'environment' => 'test',
        'debug' => true,
        'cache_router' => false,
        'em_service' => 'doctrine.orm.entity_manager',
        'rebootable_client' => true,
    ];

    
    public function _parts()
    {
        return ['services'];
    }

    
    protected $kernelClass;

    
    protected $permanentServices = [];

    
    protected $persistentServices = [];

    public function _initialize()
    {
        $cache = Configuration::projectDir() . $this->config['var_path'] . DIRECTORY_SEPARATOR . 'bootstrap.php.cache';
        if (!file_exists($cache)) {
            throw new ModuleRequireException(
                __CLASS__,
                "Symfony bootstrap file not found in $cache\n \n" .
                "Please specify path to bootstrap file using `var_path` config option\n \n" .
                "If you are trying to load bootstrap from a Bundle provide path like:\n \n" .
                "modules:\n    enabled:\n" .
                "    - Symfony:\n" .
                "        var_path: '../../app'\n" .
                "        app_path: '../../app'"
            );
        }
        require_once $cache;
        $this->kernelClass = $this->getKernelClass();
        $maxNestingLevel = 200; // Symfony may have very long nesting level
        $xdebugMaxLevelKey = 'xdebug.max_nesting_level';
        if (ini_get($xdebugMaxLevelKey) < $maxNestingLevel) {
            ini_set($xdebugMaxLevelKey, $maxNestingLevel);
        }

        $this->kernel = new $this->kernelClass($this->config['environment'], $this->config['debug']);
        $this->kernel->boot();

        if ($this->config['cache_router'] === true) {
            $this->persistService('router', true);
        }
    }

    
    public function _before(\Codeception\TestInterface $test)
    {
        $this->persistentServices = array_merge($this->persistentServices, $this->permanentServices);
        $this->client = new SymfonyConnector($this->kernel, $this->persistentServices, $this->config['rebootable_client']);
    }

    
    public function _after(\Codeception\TestInterface $test)
    {
        foreach ($this->permanentServices as $serviceName => $service) {
            $this->permanentServices[$serviceName] = $this->grabService($serviceName);
        }
        parent::_after($test);
    }

    
    public function _getEntityManager()
    {
        if ($this->kernel === null) {
            $this->fail('Symfony2 platform module is not loaded');
        }
        if (!isset($this->permanentServices[$this->config['em_service']])) {
            // try to persist configured EM
            $this->persistService($this->config['em_service'], true);

            if ($this->_getContainer()->has('doctrine')) {
                $this->persistService('doctrine', true);
            }
            if ($this->_getContainer()->has('doctrine.orm.default_entity_manager')) {
                $this->persistService('doctrine.orm.default_entity_manager', true);
            }
            if ($this->_getContainer()->has('doctrine.dbal.backend_connection')) {
                $this->persistService('doctrine.dbal.backend_connection', true);
            }
        }
        return $this->permanentServices[$this->config['em_service']];
    }

    
    public function _getContainer()
    {
        return $this->kernel->getContainer();
    }

    
    protected function getKernelClass()
    {
        $path = \Codeception\Configuration::projectDir() . $this->config['app_path'];
        if (!file_exists(\Codeception\Configuration::projectDir() . $this->config['app_path'])) {
            throw new ModuleRequireException(
                __CLASS__,
                "Can't load Kernel from $path.\n"
                . "Directory does not exists. Use `app_path` parameter to provide valid application path"
            );
        }

        $finder = new Finder();
        $finder->name('*Kernel.php')->depth('0')->in($path);
        $results = iterator_to_array($finder);
        if (!count($results)) {
            throw new ModuleRequireException(
                __CLASS__,
                "AppKernel was not found at $path. "
                . "Specify directory where Kernel class for your application is located with `app_path` parameter."
            );
        }

        $file = current($results);
        $class = $file->getBasename('.php');

        require_once $file;

        return $class;
    }

    
    public function persistService($serviceName, $isPermanent = false)
    {
        $service = $this->grabService($serviceName);
        $this->persistentServices[$serviceName] = $service;
        if ($isPermanent) {
            $this->permanentServices[$serviceName] = $service;
        }
        if ($this->client) {
            $this->client->persistentServices[$serviceName] = $service;
        }
    }

    
    public function unpersistService($serviceName)
    {
        if (isset($this->persistentServices[$serviceName])) {
            unset($this->persistentServices[$serviceName]);
        }
        if (isset($this->permanentServices[$serviceName])) {
            unset($this->permanentServices[$serviceName]);
        }
        if ($this->client && isset($this->client->persistentServices[$serviceName])) {
            unset($this->client->persistentServices[$serviceName]);
        }
    }

    
    public function invalidateCachedRouter()
    {
        $this->unpersistService('router');
    }

    
    public function amOnRoute($routeName, array $params = [])
    {
        $router = $this->grabService('router');
        if (!$router->getRouteCollection()->get($routeName)) {
            $this->fail(sprintf('Route with name "%s" does not exists.', $routeName));
        }
        $url = $router->generate($routeName, $params);
        $this->amOnPage($url);
    }

    
    public function seeCurrentRouteIs($routeName, array $params = [])
    {
        $router = $this->grabService('router');
        if (!$router->getRouteCollection()->get($routeName)) {
            $this->fail(sprintf('Route with name "%s" does not exists.', $routeName));
        }

        $uri = explode('?', $this->grabFromCurrentUrl())[0];
        try {
            $match = $router->match($uri);
        } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
            $this->fail(sprintf('The "%s" url does not match with any route', $uri));
        }
        $expected = array_merge(array('_route' => $routeName), $params);
        $intersection = array_intersect_assoc($expected, $match);

        $this->assertEquals($expected, $intersection);
    }

    
    public function seeInCurrentRoute($routeName)
    {
        $router = $this->grabService('router');
        if (!$router->getRouteCollection()->get($routeName)) {
            $this->fail(sprintf('Route with name "%s" does not exists.', $routeName));
        }

        $uri = explode('?', $this->grabFromCurrentUrl())[0];
        try {
            $matchedRouteName = $router->match($uri)['_route'];
        } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
            $this->fail(sprintf('The "%s" url does not match with any route', $uri));
        }

        $this->assertEquals($matchedRouteName, $routeName);
    }

    
    public function seeEmailIsSent()
    {
        $profile = $this->getProfile();
        if (!$profile) {
            $this->fail('Emails can\'t be tested without Profiler');
        }
        if (!$profile->hasCollector('swiftmailer')) {
            $this->fail('Emails can\'t be tested without SwiftMailer connector');
        }

        $this->assertGreaterThan(0, $profile->getCollector('swiftmailer')->getMessageCount());
    }

    
    public function grabServiceFromContainer($service)
    {
        return $this->grabService($service);
    }

    
    public function grabService($service)
    {
        $container = $this->_getContainer();
        if (!$container->has($service)) {
            $this->fail("Service $service is not available in container");
        }
        return $container->get($service);
    }

    
    protected function getProfile()
    {
        $container = $this->_getContainer();
        if (!$container->has('profiler')) {
            return null;
        }

        $profiler = $this->grabService('profiler');
        $response = $this->client->getResponse();
        if (null === $response) {
            $this->fail("You must perform a request before using this method.");
        }
        return $profiler->loadProfileFromResponse($response);
    }

    
    protected function debugResponse($url)
    {
        parent::debugResponse($url);

        if ($profile = $this->getProfile()) {
            if ($profile->hasCollector('security')) {
                if ($profile->getCollector('security')->isAuthenticated()) {
                    $roles = $profile->getCollector('security')->getRoles();

                    if ($roles instanceof Data) {
                        $roles = $this->extractRawRoles($roles);
                    }

                    $this->debugSection(
                        'User',
                        $profile->getCollector('security')->getUser()
                        . ' [' . implode(',', $roles) . ']'
                    );
                } else {
                    $this->debugSection('User', 'Anonymous');
                }
            }
            if ($profile->hasCollector('swiftmailer')) {
                $messages = $profile->getCollector('swiftmailer')->getMessageCount();
                if ($messages) {
                    $this->debugSection('Emails', $messages . ' sent');
                }
            }
            if ($profile->hasCollector('timer')) {
                $this->debugSection('Time', $profile->getCollector('timer')->getTime());
            }
        }
    }

    
    private function extractRawRoles(Data $data)
    {
        $raw = $data->getRawData();

        return isset($raw[1]) ? $raw[1] : [];
    }

    
    protected function getInternalDomains()
    {
        $internalDomains = [];

        $routes = $this->grabService('router')->getRouteCollection();
        /* @var \Symfony\Component\Routing\Route $route */
        foreach ($routes as $route) {
            if (!is_null($route->getHost())) {
                $compiled = $route->compile();
                if (!is_null($compiled->getHostRegex())) {
                    $internalDomains[] = $compiled->getHostRegex();
                }
            }
        }

        return array_unique($internalDomains);
    }

    
    public function rebootClientKernel()
    {
        if ($this->client) {
            $this->client->rebootKernel();
        }
    }
}
