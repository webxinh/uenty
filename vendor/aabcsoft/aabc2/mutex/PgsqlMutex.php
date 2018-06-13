<?php


namespace aabc\mutex;

use Aabc;
use aabc\base\InvalidConfigException;
use aabc\base\InvalidParamException;


class PgsqlMutex extends DbMutex
{
    
    public function init()
    {
        parent::init();
        if ($this->db->driverName !== 'pgsql') {
            throw new InvalidConfigException('In order to use PgsqlMutex connection must be configured to use PgSQL database.');
        }
    }

    
    private function getKeysFromName($name)
    {
        return array_values(unpack('n2', sha1($name, true)));
    }

    
    protected function acquireLock($name, $timeout = 0)
    {
        if ($timeout !== 0) {
            throw new InvalidParamException('PgsqlMutex does not support timeout.');
        }
        list($key1, $key2) = $this->getKeysFromName($name);
        return (bool) $this->db
            ->createCommand('SELECT pg_try_advisory_lock(:key1, :key2)', [':key1' => $key1, ':key2' => $key2])
            ->queryScalar();
    }

    
    protected function releaseLock($name)
    {
        list($key1, $key2) = $this->getKeysFromName($name);
        return (bool) $this->db
            ->createCommand('SELECT pg_advisory_unlock(:key1, :key2)', [':key1' => $key1, ':key2' => $key2])
            ->queryScalar();
    }
}
