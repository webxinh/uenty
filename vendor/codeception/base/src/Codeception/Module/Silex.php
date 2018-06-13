<?php
namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Framework;
use Codeception\Lib\Interfaces\DoctrineProvider;
use Codeception\TestInterface;
use Symfony\Component\HttpKernel\Client;


class Silex extends Framework implements DoctrineProvider
{
    protected $app;
    protected $requiredFields = ['app'];
    protected $config = [
        'em_service' => 'db.orm.em'
    ];

    public function _initialize()
    {
        if (!file_exists(Configuration::projectDir() . $this->config['app'])) {
            throw new ModuleConfigException(__CLASS__, "Bootstrap file {$this->config['app']} not found");
        }

        $this->loadApp();
    }

    public function _before(TestInterface $test)
    {
        $this->loadApp();
        $this->client = new Client($this->app);
    }

    public function _getEntityManager()
    {
        if (!isset($this->app[$this->config['em_service']])) {
            return null;
        }

        return $this->app[$this->config['em_service']];
    }

    protected function loadApp()
    {
        $this->app = require Configuration::projectDir() . $this->config['app'];
        // if $app is not returned but exists
        if (isset($app)) {
            $this->app = $app;
        }
        if (!isset($this->app)) {
            throw new ModuleConfigException(__CLASS__, "\$app instance was not received from bootstrap file");
        }

        // make doctrine persistent
        $db_orm_em = $this->_getEntityManager();
        if ($db_orm_em) {
            $this->app->extend($this->config['em_service'], function () use ($db_orm_em) {
                return $db_orm_em;
            });
        }

        // some silex apps (like bolt) may rely on global $app variable
        $GLOBALS['app'] = $this->app;
    }

    
    public function grabService($service)
    {
        return $this->app[$service];
    }

    
    public function getInternalDomains()
    {
        $internalDomains = [];

        foreach ($this->app['routes'] as $route) {
            if ($domain = $route->getHost()) {
                $internalDomains[] = '/^' . preg_quote($domain, '/') . '$/';
            }
        }

        return $internalDomains;
    }
}
