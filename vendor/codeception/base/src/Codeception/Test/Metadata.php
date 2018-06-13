<?php
namespace Codeception\Test;

use Codeception\Exception\InjectionException;

class Metadata
{
    protected $name;
    protected $filename;
    protected $feature;

    protected $env = [];
    protected $groups = [];
    protected $dependencies = [];
    protected $skip = null;
    protected $incomplete = null;

    protected $current = [];
    protected $services = [];
    protected $reports = [];

    
    public function getEnv()
    {
        return $this->env;
    }

    
    public function setEnv($env)
    {
        $this->env = $env;
    }

    
    public function getGroups()
    {
        return array_unique($this->groups);
    }

    
    public function setGroups($groups)
    {
        $this->groups = array_merge($this->groups, $groups);
    }

    
    public function getSkip()
    {
        return $this->skip;
    }

    
    public function setSkip($skip)
    {
        $this->skip = $skip;
    }

    
    public function getIncomplete()
    {
        return $this->incomplete;
    }

    
    public function setIncomplete($incomplete)
    {
        $this->incomplete = $incomplete;
    }

    
    public function getCurrent($key = null)
    {
        if ($key && isset($this->current[$key])) {
            return $this->current[$key];
        }
        if ($key) {
            return null;
        }
        return $this->current;
    }

    public function setCurrent(array $currents)
    {
        $this->current = array_merge($this->current, $currents);
    }

    
    public function getName()
    {
        return $this->name;
    }

    
    public function setName($name)
    {
        $this->name = $name;
    }

    
    public function getFilename()
    {
        return $this->filename;
    }

    
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    
    public function getDependencies()
    {
        return $this->dependencies;
    }

    
    public function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;
    }

    public function isBlocked()
    {
        return $this->skip !== null || $this->incomplete !== null;
    }

    
    public function getFeature()
    {
        return $this->feature;
    }

    
    public function setFeature($feature)
    {
        $this->feature = $feature;
    }

    
    public function getService($service)
    {
        if (!isset($this->services[$service])) {
            throw new InjectionException("Service $service is not defined and can't be accessed from a test");
        }
        return $this->services[$service];
    }

    
    public function setServices($services)
    {
        $this->services = $services;
    }

    
    public function getReports()
    {
        return $this->reports;
    }

    
    public function addReport($type, $report)
    {
        $this->reports[$type] = $report;
    }
}
