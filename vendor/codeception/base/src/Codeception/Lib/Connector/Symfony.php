<?php
namespace Codeception\Lib\Connector;

class Symfony extends \Symfony\Component\HttpKernel\Client
{
    
    private $rebootable = true;

    
    private $hasPerformedRequest = false;

    
    private $container = null;

    
    public $persistentServices = [];

    
    public function __construct(\Symfony\Component\HttpKernel\Kernel $kernel, array $services = [], $rebootable = true)
    {
        parent::__construct($kernel);
        $this->followRedirects(true);
        $this->rebootable = (boolean)$rebootable;
        $this->persistentServices = $services;
        $this->rebootKernel();
    }

    
    protected function doRequest($request)
    {
        if ($this->rebootable) {
            if ($this->hasPerformedRequest) {
                $this->rebootKernel();
            } else {
                $this->hasPerformedRequest = true;
            }
        }
        return parent::doRequest($request);
    }

    
    public function rebootKernel()
    {
        if ($this->container) {
            foreach ($this->persistentServices as $serviceName => $service) {
                if ($this->container->has($serviceName)) {
                    $this->persistentServices[$serviceName] = $this->container->get($serviceName);
                }
            }
        }

        $this->kernel->shutdown();
        $this->kernel->boot();
        $this->container = $this->kernel->getContainer();

        if ($this->container->has('profiler')) {
            $this->container->get('profiler')->enable();
        }

        foreach ($this->persistentServices as $serviceName => $service) {
            $this->container->set($serviceName, $service);
        }
    }
}
