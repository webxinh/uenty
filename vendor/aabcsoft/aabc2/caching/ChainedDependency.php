<?php


namespace aabc\caching;


class ChainedDependency extends Dependency
{
    
    public $dependencies = [];
    
    public $dependOnAll = true;


    
    public function evaluateDependency($cache)
    {
        foreach ($this->dependencies as $dependency) {
            $dependency->evaluateDependency($cache);
        }
    }

    
    protected function generateDependencyData($cache)
    {
        return null;
    }

    
    public function isChanged($cache)
    {
        foreach ($this->dependencies as $dependency) {
            if ($this->dependOnAll && $dependency->isChanged($cache)) {
                return true;
            } elseif (!$this->dependOnAll && !$dependency->isChanged($cache)) {
                return false;
            }
        }
        return !$this->dependOnAll;
    }
}
