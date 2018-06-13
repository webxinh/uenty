<?php
namespace Codeception\Module;

use Codeception\Lib\Framework;
use Codeception\TestInterface;
use Codeception\Configuration;
use Codeception\Lib\Connector\ZendExpressive as ZendExpressiveConnector;
use Psr\Http\Message\ResponseInterface;


class ZendExpressive extends Framework
{
    protected $config = [
        'container' => 'config/container.php',
    ];

    
    public $client;

    
    public $container;

    
    public $application;

    protected $responseCollector;

    public function _initialize()
    {
        $cwd = getcwd();
        chdir(Configuration::projectDir());
        $this->container = require Configuration::projectDir() . $this->config['container'];
        chdir($cwd);
        $this->application = $this->container->get('Zend\Expressive\Application');
        $this->initResponseCollector();
    }

    public function _before(TestInterface $test)
    {
        $this->client = new ZendExpressiveConnector();
        $this->client->setApplication($this->application);
        $this->client->setResponseCollector($this->responseCollector);
    }

    public function _after(TestInterface $test)
    {
        //Close the session, if any are open
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        parent::_after($test);
    }

    private function initResponseCollector()
    {
        
        $emitterStack = $this->application->getEmitter();
        while (!$emitterStack->isEmpty()) {
            $emitterStack->pop();
        }

        $this->responseCollector = new ZendExpressiveConnector\ResponseCollector;
        $emitterStack->unshift($this->responseCollector);
    }
}
