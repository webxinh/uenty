<?php


namespace aabc\caching;

use Aabc;
use aabc\base\InvalidConfigException;
use aabc\db\Connection;
use aabc\di\Instance;


class DbDependency extends Dependency
{
    
    public $db = 'db';
    
    public $sql;
    
    public $params = [];


    
    protected function generateDependencyData($cache)
    {
        /* @var $db Connection */
        $db = Instance::ensure($this->db, Connection::className());
        if ($this->sql === null) {
            throw new InvalidConfigException('DbDependency::sql must be set.');
        }

        if ($db->enableQueryCache) {
            // temporarily disable and re-enable query caching
            $db->enableQueryCache = false;
            $result = $db->createCommand($this->sql, $this->params)->queryOne();
            $db->enableQueryCache = true;
        } else {
            $result = $db->createCommand($this->sql, $this->params)->queryOne();
        }

        return $result;
    }
}