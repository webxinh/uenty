<?php

namespace Codeception\Lib;


abstract class Framework extends InnerBrowser
{
    
    protected function getInternalDomains()
    {
        return [];
    }

    public function _beforeSuite($settings = [])
    {
        
        $this->internalDomains = null;
    }
}
