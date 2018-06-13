<?php


namespace aabc\caching;

use Aabc;
use aabc\base\InvalidConfigException;
use aabc\db\Connection;
use aabc\db\Query;
use aabc\di\Instance;


class DbCache extends Cache
{
    
    public $db = 'db';
    
    public $cacheTable = '{{%cache}}';
    
    public $gcProbability = 100;


    
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
    }

    
    public function exists($key)
    {
        $key = $this->buildKey($key);

        $query = new Query;
        $query->select(['COUNT(*)'])
            ->from($this->cacheTable)
            ->where('[[id]] = :id AND ([[expire]] = 0 OR [[expire]] >' . time() . ')', [':id' => $key]);
        if ($this->db->enableQueryCache) {
            // temporarily disable and re-enable query caching
            $this->db->enableQueryCache = false;
            $result = $query->createCommand($this->db)->queryScalar();
            $this->db->enableQueryCache = true;
        } else {
            $result = $query->createCommand($this->db)->queryScalar();
        }

        return $result > 0;
    }

    
    protected function getValue($key)
    {
        $query = new Query;
        $query->select(['data'])
            ->from($this->cacheTable)
            ->where('[[id]] = :id AND ([[expire]] = 0 OR [[expire]] >' . time() . ')', [':id' => $key]);
        if ($this->db->enableQueryCache) {
            // temporarily disable and re-enable query caching
            $this->db->enableQueryCache = false;
            $result = $query->createCommand($this->db)->queryScalar();
            $this->db->enableQueryCache = true;

            return $result;
        } else {
            return $query->createCommand($this->db)->queryScalar();
        }
    }

    
    protected function getValues($keys)
    {
        if (empty($keys)) {
            return [];
        }
        $query = new Query;
        $query->select(['id', 'data'])
            ->from($this->cacheTable)
            ->where(['id' => $keys])
            ->andWhere('([[expire]] = 0 OR [[expire]] > ' . time() . ')');

        if ($this->db->enableQueryCache) {
            $this->db->enableQueryCache = false;
            $rows = $query->createCommand($this->db)->queryAll();
            $this->db->enableQueryCache = true;
        } else {
            $rows = $query->createCommand($this->db)->queryAll();
        }

        $results = [];
        foreach ($keys as $key) {
            $results[$key] = false;
        }
        foreach ($rows as $row) {
            $results[$row['id']] = $row['data'];
        }

        return $results;
    }

    
    protected function setValue($key, $value, $duration)
    {
        $command = $this->db->createCommand()
            ->update($this->cacheTable, [
                'expire' => $duration > 0 ? $duration + time() : 0,
                'data' => [$value, \PDO::PARAM_LOB],
            ], ['id' => $key]);

        if ($command->execute()) {
            $this->gc();

            return true;
        } else {
            return $this->addValue($key, $value, $duration);
        }
    }

    
    protected function addValue($key, $value, $duration)
    {
        $this->gc();

        try {
            $this->db->createCommand()
                ->insert($this->cacheTable, [
                    'id' => $key,
                    'expire' => $duration > 0 ? $duration + time() : 0,
                    'data' => [$value, \PDO::PARAM_LOB],
                ])->execute();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    
    protected function deleteValue($key)
    {
        $this->db->createCommand()
            ->delete($this->cacheTable, ['id' => $key])
            ->execute();

        return true;
    }

    
    public function gc($force = false)
    {
        if ($force || mt_rand(0, 1000000) < $this->gcProbability) {
            $this->db->createCommand()
                ->delete($this->cacheTable, '[[expire]] > 0 AND [[expire]] < ' . time())
                ->execute();
        }
    }

    
    protected function flushValues()
    {
        $this->db->createCommand()
            ->delete($this->cacheTable)
            ->execute();

        return true;
    }
}
