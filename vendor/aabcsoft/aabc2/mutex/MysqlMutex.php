<?php


namespace aabc\mutex;

use Aabc;
use aabc\base\InvalidConfigException;


class MysqlMutex extends DbMutex
{
    
    public function init()
    {
        parent::init();
        if ($this->db->driverName !== 'mysql') {
            throw new InvalidConfigException('In order to use MysqlMutex connection must be configured to use MySQL database.');
        }
    }

    
    protected function acquireLock($name, $timeout = 0)
    {
        return (bool) $this->db
            ->createCommand('SELECT GET_LOCK(:name, :timeout)', [':name' => $name, ':timeout' => $timeout])
            ->queryScalar();
    }

    
    protected function releaseLock($name)
    {
        return (bool) $this->db
            ->createCommand('SELECT RELEASE_LOCK(:name)', [':name' => $name])
            ->queryScalar();
    }
}
