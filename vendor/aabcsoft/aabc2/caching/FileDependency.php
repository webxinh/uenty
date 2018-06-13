<?php


namespace aabc\caching;

use Aabc;
use aabc\base\InvalidConfigException;


class FileDependency extends Dependency
{
    
    public $fileName;


    
    protected function generateDependencyData($cache)
    {
        if ($this->fileName === null) {
            throw new InvalidConfigException('FileDependency::fileName must be set');
        }

        $fileName = Aabc::getAlias($this->fileName);
        clearstatcache(false, $fileName);
        return @filemtime($fileName);
    }
}
