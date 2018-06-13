<?php


namespace aabc\db;

use Aabc;
use aabc\base\Component;


class Query extends Component implements QueryInterface
{
    use QueryTrait;

    
    public $select;
    
    public $selectOption;
    
    public $distinct;
    
    public $from;
    
    public $groupBy;
    
    public $join;
    
    public $having;
    
    public $union;
    
    public $params = [];


    
    public function createCommand($db = null)
    {
        if ($db === null) {
            $db = Aabc::$app->getDb();
        }
        list ($sql, $params) = $db->getQueryBuilder()->build($this);

        return $db->createCommand($sql, $params);
    }

    
    public function prepare($builder)
    {
        return $this;
    }

    
    public function batch($batchSize = 100, $db = null)
    {
        return Aabc::createObject([
            'class' => BatchQueryResult::className(),
            'query' => $this,
            'batchSize' => $batchSize,
            'db' => $db,
            'each' => false,
        ]);
    }

    
    public function each($batchSize = 100, $db = null)
    {
        return Aabc::createObject([
            'class' => BatchQueryResult::className(),
            'query' => $this,
            'batchSize' => $batchSize,
            'db' => $db,
            'each' => true,
        ]);
    }

    
    public function all($db = null)
    {
        if ($this->emulateExecution) {
            return [];
        }
        $rows = $this->createCommand($db)->queryAll();
        return $this->populate($rows);
    }

    
    public function populate($rows)
    {
        if ($this->indexBy === null) {
            return $rows;
        }
        $result = [];
        foreach ($rows as $row) {
            if (is_string($this->indexBy)) {
                $key = $row[$this->indexBy];
            } else {
                $key = call_user_func($this->indexBy, $row);
            }
            $result[$key] = $row;
        }
        return $result;
    }

    
    public function one($db = null)
    {
        if ($this->emulateExecution) {
            return false;
        }
        return $this->createCommand($db)->queryOne();
    }

    
    public function scalar($db = null)
    {
        if ($this->emulateExecution) {
            return null;
        }
        return $this->createCommand($db)->queryScalar();
    }

    
    public function column($db = null)
    {
        if ($this->emulateExecution) {
            return [];
        }

        if ($this->indexBy === null) {
            return $this->createCommand($db)->queryColumn();
        }

        if (is_string($this->indexBy) && is_array($this->select) && count($this->select) === 1) {
            $this->select[] = $this->indexBy;
        }
        $rows = $this->createCommand($db)->queryAll();
        $results = [];
        foreach ($rows as $row) {
            $value = reset($row);

            if ($this->indexBy instanceof \Closure) {
                $results[call_user_func($this->indexBy, $row)] = $value;
            } else {
                $results[$row[$this->indexBy]] = $value;
            }
        }
        return $results;
    }

    
    public function count($q = '*', $db = null)
    {
        if ($this->emulateExecution) {
            return 0;
        }
        return $this->queryScalar("COUNT($q)", $db);
    }

    
    public function sum($q, $db = null)
    {
        if ($this->emulateExecution) {
            return 0;
        }
        return $this->queryScalar("SUM($q)", $db);
    }

    
    public function average($q, $db = null)
    {
        if ($this->emulateExecution) {
            return 0;
        }
        return $this->queryScalar("AVG($q)", $db);
    }

    
    public function min($q, $db = null)
    {
        return $this->queryScalar("MIN($q)", $db);
    }

    
    public function max($q, $db = null)
    {
        return $this->queryScalar("MAX($q)", $db);
    }

    
    public function exists($db = null)
    {
        if ($this->emulateExecution) {
            return false;
        }
        $command = $this->createCommand($db);
        $params = $command->params;
        $command->setSql($command->db->getQueryBuilder()->selectExists($command->getSql()));
        $command->bindValues($params);
        return (bool) $command->queryScalar();
    }

    
    protected function queryScalar($selectExpression, $db)
    {
        if ($this->emulateExecution) {
            return null;
        }

        $select = $this->select;
        $limit = $this->limit;
        $offset = $this->offset;

        $this->select = [$selectExpression];
        $this->limit = null;
        $this->offset = null;
        $command = $this->createCommand($db);

        $this->select = $select;
        $this->limit = $limit;
        $this->offset = $offset;

        if (
            !$this->distinct
            && empty($this->groupBy)
            && empty($this->having)
            && empty($this->union)
            && empty($this->orderBy)
        ) {
            return $command->queryScalar();
        } else {
            return (new Query)->select([$selectExpression])
                ->from(['c' => $this])
                ->createCommand($command->db)
                ->queryScalar();
        }
    }

    
    public function select($columns, $option = null)
    {
        if ($columns instanceof Expression) {
            $columns = [$columns];
        } elseif (!is_array($columns)) {
            $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
        }
        $this->select = $columns;
        $this->selectOption = $option;
        return $this;
    }

    
    public function addSelect($columns)
    {
        if ($columns instanceof Expression) {
            $columns = [$columns];
        } elseif (!is_array($columns)) {
            $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
        }
        if ($this->select === null) {
            $this->select = $columns;
        } else {
            $this->select = array_merge($this->select, $columns);
        }
        return $this;
    }

    
    public function distinct($value = true)
    {
        $this->distinct = $value;
        return $this;
    }

    
    public function from($tables)
    {
        if (!is_array($tables)) {
            $tables = preg_split('/\s*,\s*/', trim($tables), -1, PREG_SPLIT_NO_EMPTY);
        }
        $this->from = $tables;
        return $this;
    }

    
    public function where($condition, $params = [])
    {
        $this->where = $condition;
        $this->addParams($params);
        return $this;
    }

    
    public function andWhere($condition, $params = [])
    {
        if ($this->where === null) {
            $this->where = $condition;
        } elseif (is_array($this->where) && isset($this->where[0]) && strcasecmp($this->where[0], 'and') === 0) {
            $this->where[] = $condition;
        } else {
            $this->where = ['and', $this->where, $condition];
        }
        $this->addParams($params);
        return $this;
    }

    
    public function orWhere($condition, $params = [])
    {
        if ($this->where === null) {
            $this->where = $condition;
        } else {
            $this->where = ['or', $this->where, $condition];
        }
        $this->addParams($params);
        return $this;
    }

    
    public function andFilterCompare($name, $value, $defaultOperator = '=')
    {
        if (preg_match('/^(<>|>=|>|<=|<|=)/', $value, $matches)) {
            $operator = $matches[1];
            $value = substr($value, strlen($operator));
        } else {
            $operator = $defaultOperator;
        }
        return $this->andFilterWhere([$operator, $name, $value]);
    }

    
    public function join($type, $table, $on = '', $params = [])
    {
        $this->join[] = [$type, $table, $on];
        return $this->addParams($params);
    }

    
    public function innerJoin($table, $on = '', $params = [])
    {
        $this->join[] = ['INNER JOIN', $table, $on];
        return $this->addParams($params);
    }

    
    public function leftJoin($table, $on = '', $params = [])
    {
        $this->join[] = ['LEFT JOIN', $table, $on];
        return $this->addParams($params);
    }

    
    public function rightJoin($table, $on = '', $params = [])
    {
        $this->join[] = ['RIGHT JOIN', $table, $on];
        return $this->addParams($params);
    }

    
    public function groupBy($columns)
    {
        if ($columns instanceof Expression) {
            $columns = [$columns];
        } elseif (!is_array($columns)) {
            $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
        }
        $this->groupBy = $columns;
        return $this;
    }

    
    public function addGroupBy($columns)
    {
        if ($columns instanceof Expression) {
            $columns = [$columns];
        } elseif (!is_array($columns)) {
            $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
        }
        if ($this->groupBy === null) {
            $this->groupBy = $columns;
        } else {
            $this->groupBy = array_merge($this->groupBy, $columns);
        }
        return $this;
    }

    
    public function having($condition, $params = [])
    {
        $this->having = $condition;
        $this->addParams($params);
        return $this;
    }

    
    public function andHaving($condition, $params = [])
    {
        if ($this->having === null) {
            $this->having = $condition;
        } else {
            $this->having = ['and', $this->having, $condition];
        }
        $this->addParams($params);
        return $this;
    }

    
    public function orHaving($condition, $params = [])
    {
        if ($this->having === null) {
            $this->having = $condition;
        } else {
            $this->having = ['or', $this->having, $condition];
        }
        $this->addParams($params);
        return $this;
    }

    
    public function filterHaving(array $condition)
    {
        $condition = $this->filterCondition($condition);
        if ($condition !== []) {
            $this->having($condition);
        }
        return $this;
    }

    
    public function andFilterHaving(array $condition)
    {
        $condition = $this->filterCondition($condition);
        if ($condition !== []) {
            $this->andHaving($condition);
        }
        return $this;
    }

    
    public function orFilterHaving(array $condition)
    {
        $condition = $this->filterCondition($condition);
        if ($condition !== []) {
            $this->orHaving($condition);
        }
        return $this;
    }

    
    public function union($sql, $all = false)
    {
        $this->union[] = ['query' => $sql, 'all' => $all];
        return $this;
    }

    
    public function params($params)
    {
        $this->params = $params;
        return $this;
    }

    
    public function addParams($params)
    {
        if (!empty($params)) {
            if (empty($this->params)) {
                $this->params = $params;
            } else {
                foreach ($params as $name => $value) {
                    if (is_int($name)) {
                        $this->params[] = $value;
                    } else {
                        $this->params[$name] = $value;
                    }
                }
            }
        }
        return $this;
    }

    
    public static function create($from)
    {
        return new self([
            'where' => $from->where,
            'limit' => $from->limit,
            'offset' => $from->offset,
            'orderBy' => $from->orderBy,
            'indexBy' => $from->indexBy,
            'select' => $from->select,
            'selectOption' => $from->selectOption,
            'distinct' => $from->distinct,
            'from' => $from->from,
            'groupBy' => $from->groupBy,
            'join' => $from->join,
            'having' => $from->having,
            'union' => $from->union,
            'params' => $from->params,
        ]);
    }
}
