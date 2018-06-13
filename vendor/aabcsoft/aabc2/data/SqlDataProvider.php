<?php


namespace aabc\data;

use Aabc;
use aabc\base\InvalidConfigException;
use aabc\db\Connection;
use aabc\db\Expression;
use aabc\di\Instance;


class SqlDataProvider extends BaseDataProvider
{
    
    public $db = 'db';
    
    public $sql;
    
    public $params = [];
    
    public $key;


    
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
        if ($this->sql === null) {
            throw new InvalidConfigException('The "sql" property must be set.');
        }
    }

    
    protected function prepareModels()
    {
        $sort = $this->getSort();
        $pagination = $this->getPagination();
        if ($pagination === false && $sort === false) {
            return $this->db->createCommand($this->sql, $this->params)->queryAll();
        }

        $sql = $this->sql;
        $orders = [];
        $limit = $offset = null;

        if ($sort !== false) {
            $orders = $sort->getOrders();
            $pattern = '/\s+order\s+by\s+([\w\s,\.]+)$/i';
            if (preg_match($pattern, $sql, $matches)) {
                array_unshift($orders, new Expression($matches[1]));
                $sql = preg_replace($pattern, '', $sql);
            }
        }

        if ($pagination !== false) {
            $pagination->totalCount = $this->getTotalCount();
            $limit = $pagination->getLimit();
            $offset = $pagination->getOffset();
        }

        $sql = $this->db->getQueryBuilder()->buildOrderByAndLimit($sql, $orders, $limit, $offset);

        return $this->db->createCommand($sql, $this->params)->queryAll();
    }

    
    protected function prepareKeys($models)
    {
        $keys = [];
        if ($this->key !== null) {
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }

            return $keys;
        } else {
            return array_keys($models);
        }
    }

    
    protected function prepareTotalCount()
    {
        return 0;
    }
}
