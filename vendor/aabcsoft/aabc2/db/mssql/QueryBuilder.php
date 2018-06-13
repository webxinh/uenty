<?php


namespace aabc\db\mssql;

use aabc\base\InvalidParamException;
use aabc\base\NotSupportedException;
use aabc\db\Expression;


class QueryBuilder extends \aabc\db\QueryBuilder
{
    
    public $typeMap = [
        Schema::TYPE_PK => 'int IDENTITY PRIMARY KEY',
        Schema::TYPE_UPK => 'int IDENTITY PRIMARY KEY',
        Schema::TYPE_BIGPK => 'bigint IDENTITY PRIMARY KEY',
        Schema::TYPE_UBIGPK => 'bigint IDENTITY PRIMARY KEY',
        Schema::TYPE_CHAR => 'nchar(1)',
        Schema::TYPE_STRING => 'nvarchar(255)',
        Schema::TYPE_TEXT => 'nvarchar(max)',
        Schema::TYPE_SMALLINT => 'smallint',
        Schema::TYPE_INTEGER => 'int',
        Schema::TYPE_BIGINT => 'bigint',
        Schema::TYPE_FLOAT => 'float',
        Schema::TYPE_DOUBLE => 'float',
        Schema::TYPE_DECIMAL => 'decimal',
        Schema::TYPE_DATETIME => 'datetime',
        Schema::TYPE_TIMESTAMP => 'datetime',
        Schema::TYPE_TIME => 'time',
        Schema::TYPE_DATE => 'date',
        Schema::TYPE_BINARY => 'varbinary(max)',
        Schema::TYPE_BOOLEAN => 'bit',
        Schema::TYPE_MONEY => 'decimal(19,4)',
    ];


    
    public function buildOrderByAndLimit($sql, $orderBy, $limit, $offset)
    {
        if (!$this->hasOffset($offset) && !$this->hasLimit($limit)) {
            $orderBy = $this->buildOrderBy($orderBy);
            return $orderBy === '' ? $sql : $sql . $this->separator . $orderBy;
        }

        if ($this->isOldMssql()) {
            return $this->oldBuildOrderByAndLimit($sql, $orderBy, $limit, $offset);
        } else {
            return $this->newBuildOrderByAndLimit($sql, $orderBy, $limit, $offset);
        }
    }

    
    protected function newBuildOrderByAndLimit($sql, $orderBy, $limit, $offset)
    {
        $orderBy = $this->buildOrderBy($orderBy);
        if ($orderBy === '') {
            // ORDER BY clause is required when FETCH and OFFSET are in the SQL
            $orderBy = 'ORDER BY (SELECT NULL)';
        }
        $sql .= $this->separator . $orderBy;

        // http://technet.microsoft.com/en-us/library/gg699618.aspx
        $offset = $this->hasOffset($offset) ? $offset : '0';
        $sql .= $this->separator . "OFFSET $offset ROWS";
        if ($this->hasLimit($limit)) {
            $sql .= $this->separator . "FETCH NEXT $limit ROWS ONLY";
        }

        return $sql;
    }

    
    protected function oldBuildOrderByAndLimit($sql, $orderBy, $limit, $offset)
    {
        $orderBy = $this->buildOrderBy($orderBy);
        if ($orderBy === '') {
            // ROW_NUMBER() requires an ORDER BY clause
            $orderBy = 'ORDER BY (SELECT NULL)';
        }

        $sql = preg_replace('/^([\s(])*SELECT(\s+DISTINCT)?(?!\s*TOP\s*\()/i', "\\1SELECT\\2 rowNum = ROW_NUMBER() over ($orderBy),", $sql);

        if ($this->hasLimit($limit)) {
            $sql = "SELECT TOP $limit * FROM ($sql) sub";
        } else {
            $sql = "SELECT * FROM ($sql) sub";
        }
        if ($this->hasOffset($offset)) {
            $sql .= $this->separator . "WHERE rowNum > $offset";
        }

        return $sql;
    }

    
    public function renameTable($oldName, $newName)
    {
        return 'sp_rename ' . $this->db->quoteTableName($oldName) . ', ' . $this->db->quoteTableName($newName);
    }

    
    public function renameColumn($table, $oldName, $newName)
    {
        $table = $this->db->quoteTableName($table);
        $oldName = $this->db->quoteColumnName($oldName);
        $newName = $this->db->quoteColumnName($newName);
        return "sp_rename '{$table}.{$oldName}', {$newName}, 'COLUMN'";
    }

    
    public function alterColumn($table, $column, $type)
    {
        $type = $this->getColumnType($type);
        $sql = 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' ALTER COLUMN '
            . $this->db->quoteColumnName($column) . ' '
            . $this->getColumnType($type);

        return $sql;
    }

    
    public function checkIntegrity($check = true, $schema = '', $table = '')
    {
        if ($schema !== '') {
            $table = "{$schema}.{$table}";
        }
        $table = $this->db->quoteTableName($table);
        if ($this->db->getTableSchema($table) === null) {
            throw new InvalidParamException("Table not found: $table");
        }
        $enable = $check ? 'CHECK' : 'NOCHECK';

        return "ALTER TABLE {$table} {$enable} CONSTRAINT ALL";
    }

    
    public function addCommentOnColumn($table, $column, $comment)
    {
        return "sp_updateextendedproperty @name = N'MS_Description', @value = {$this->db->quoteValue($comment)}, @level1type = N'Table',  @level1name = {$this->db->quoteTableName($table)}, @level2type = N'Column', @level2name = {$this->db->quoteColumnName($column)}";
    }

    
    public function addCommentOnTable($table, $comment)
    {
        return "sp_updateextendedproperty @name = N'MS_Description', @value = {$this->db->quoteValue($comment)}, @level1type = N'Table',  @level1name = {$this->db->quoteTableName($table)}";
    }

    
    public function dropCommentFromColumn($table, $column)
    {
        return "sp_dropextendedproperty @name = N'MS_Description', @level1type = N'Table',  @level1name = {$this->db->quoteTableName($table)}, @level2type = N'Column', @level2name = {$this->db->quoteColumnName($column)}";
    }

    
    public function dropCommentFromTable($table)
    {
        return "sp_dropextendedproperty @name = N'MS_Description', @level1type = N'Table',  @level1name = {$this->db->quoteTableName($table)}";
    }

    
    protected function getAllColumnNames($modelClass = null)
    {
        if (!$modelClass) {
            return null;
        }
        /* @var $model \aabc\db\ActiveRecord */
        $model = new $modelClass;
        $schema = $model->getTableSchema();
        return array_keys($schema->columns);
    }

    
    private $_oldMssql;

    
    protected function isOldMssql()
    {
        if ($this->_oldMssql === null) {
            $pdo = $this->db->getSlavePdo();
            $version = explode('.', $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION));
            $this->_oldMssql = $version[0] < 11;
        }
        return $this->_oldMssql;
    }

    
    protected function buildSubqueryInCondition($operator, $columns, $values, &$params)
    {
        if (is_array($columns)) {
            throw new NotSupportedException(__METHOD__ . ' is not supported by MSSQL.');
        }
        return parent::buildSubqueryInCondition($operator, $columns, $values, $params);
    }

    
    protected function buildCompositeInCondition($operator, $columns, $values, &$params)
    {
        $quotedColumns = [];
        foreach ($columns as $i => $column) {
            $quotedColumns[$i] = strpos($column, '(') === false ? $this->db->quoteColumnName($column) : $column;
        }
        $vss = [];
        foreach ($values as $value) {
            $vs = [];
            foreach ($columns as $i => $column) {
                if (isset($value[$column])) {
                    $phName = self::PARAM_PREFIX . count($params);
                    $params[$phName] = $value[$column];
                    $vs[] = $quotedColumns[$i] . ($operator === 'IN' ? ' = ' : ' != ') . $phName;
                } else {
                    $vs[] = $quotedColumns[$i] . ($operator === 'IN' ? ' IS' : ' IS NOT') . ' NULL';
                }
            }
            $vss[] = '(' . implode($operator === 'IN' ? ' AND ' : ' OR ', $vs) . ')';
        }

        return '(' . implode($operator === 'IN' ? ' OR ' : ' AND ', $vss) . ')';
    }

    
    public function selectExists($rawSql)
    {
        return 'SELECT CASE WHEN EXISTS(' . $rawSql . ') THEN 1 ELSE 0 END';
    }

    
    private function normalizeTableRowData($table, $columns, &$params)
    {
        if (($tableSchema = $this->db->getSchema()->getTableSchema($table)) !== null) {
            $columnSchemas = $tableSchema->columns;
            foreach ($columns as $name => $value) {
                // @see https://github.com/aabcsoft/aabc2/issues/12599
                if (isset($columnSchemas[$name]) && $columnSchemas[$name]->type === Schema::TYPE_BINARY && $columnSchemas[$name]->dbType === 'varbinary' && is_string($value)) {
                    $phName = self::PARAM_PREFIX . count($params);
                    $columns[$name] = new Expression("CONVERT(VARBINARY, $phName)", [$phName => $value]);
                }
            }
        }
        return $columns;
    }

    
    public function insert($table, $columns, &$params)
    {
        return parent::insert($table, $this->normalizeTableRowData($table, $columns, $params), $params);
    }

    
    public function update($table, $columns, $condition, &$params)
    {
        return parent::update($table, $this->normalizeTableRowData($table, $columns, $params), $condition, $params);
    }
}
