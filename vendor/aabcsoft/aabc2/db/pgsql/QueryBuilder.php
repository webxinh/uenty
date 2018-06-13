<?php


namespace aabc\db\pgsql;

use aabc\base\InvalidParamException;


class QueryBuilder extends \aabc\db\QueryBuilder
{
    
    const INDEX_UNIQUE = 'unique';
    
    const INDEX_B_TREE = 'btree';
    
    const INDEX_HASH = 'hash';
    
    const INDEX_GIST = 'gist';
    
    const INDEX_GIN = 'gin';

    
    public $typeMap = [
        Schema::TYPE_PK => 'serial NOT NULL PRIMARY KEY',
        Schema::TYPE_UPK => 'serial NOT NULL PRIMARY KEY',
        Schema::TYPE_BIGPK => 'bigserial NOT NULL PRIMARY KEY',
        Schema::TYPE_UBIGPK => 'bigserial NOT NULL PRIMARY KEY',
        Schema::TYPE_CHAR => 'char(1)',
        Schema::TYPE_STRING => 'varchar(255)',
        Schema::TYPE_TEXT => 'text',
        Schema::TYPE_SMALLINT => 'smallint',
        Schema::TYPE_INTEGER => 'integer',
        Schema::TYPE_BIGINT => 'bigint',
        Schema::TYPE_FLOAT => 'double precision',
        Schema::TYPE_DOUBLE => 'double precision',
        Schema::TYPE_DECIMAL => 'numeric(10,0)',
        Schema::TYPE_DATETIME => 'timestamp(0)',
        Schema::TYPE_TIMESTAMP => 'timestamp(0)',
        Schema::TYPE_TIME => 'time(0)',
        Schema::TYPE_DATE => 'date',
        Schema::TYPE_BINARY => 'bytea',
        Schema::TYPE_BOOLEAN => 'boolean',
        Schema::TYPE_MONEY => 'numeric(19,4)',
    ];

    
    protected $conditionBuilders = [
        'NOT' => 'buildNotCondition',
        'AND' => 'buildAndCondition',
        'OR' => 'buildAndCondition',
        'BETWEEN' => 'buildBetweenCondition',
        'NOT BETWEEN' => 'buildBetweenCondition',
        'IN' => 'buildInCondition',
        'NOT IN' => 'buildInCondition',
        'LIKE' => 'buildLikeCondition',
        'ILIKE' => 'buildLikeCondition',
        'NOT LIKE' => 'buildLikeCondition',
        'NOT ILIKE' => 'buildLikeCondition',
        'OR LIKE' => 'buildLikeCondition',
        'OR ILIKE' => 'buildLikeCondition',
        'OR NOT LIKE' => 'buildLikeCondition',
        'OR NOT ILIKE' => 'buildLikeCondition',
        'EXISTS' => 'buildExistsCondition',
        'NOT EXISTS' => 'buildExistsCondition',
    ];


    
    public function createIndex($name, $table, $columns, $unique = false)
    {
        if ($unique === self::INDEX_UNIQUE || $unique === true) {
            $index = false;
            $unique = true;
        } else {
            $index = $unique;
            $unique = false;
        }

        return ($unique ? 'CREATE UNIQUE INDEX ' : 'CREATE INDEX ') .
        $this->db->quoteTableName($name) . ' ON ' .
        $this->db->quoteTableName($table) .
        ($index !== false ? " USING $index" : '') .
        ' (' . $this->buildColumns($columns) . ')';
    }

    
    public function dropIndex($name, $table)
    {
        return 'DROP INDEX ' . $this->db->quoteTableName($name);
    }

    
    public function renameTable($oldName, $newName)
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($oldName) . ' RENAME TO ' . $this->db->quoteTableName($newName);
    }

    
    public function resetSequence($tableName, $value = null)
    {
        $table = $this->db->getTableSchema($tableName);
        if ($table !== null && $table->sequenceName !== null) {
            // c.f. http://www.postgresql.org/docs/8.1/static/functions-sequence.html
            $sequence = $this->db->quoteTableName($table->sequenceName);
            $tableName = $this->db->quoteTableName($tableName);
            if ($value === null) {
                $key = reset($table->primaryKey);
                $value = "(SELECT COALESCE(MAX(\"{$key}\"),0) FROM {$tableName})+1";
            } else {
                $value = (int) $value;
            }

            return "SELECT SETVAL('$sequence',$value,false)";
        } elseif ($table === null) {
            throw new InvalidParamException("Table not found: $tableName");
        } else {
            throw new InvalidParamException("There is not sequence associated with table '$tableName'.");
        }
    }

    
    public function checkIntegrity($check = true, $schema = '', $table = '')
    {
        $enable = $check ? 'ENABLE' : 'DISABLE';
        $schema = $schema ? $schema : $this->db->getSchema()->defaultSchema;
        $tableNames = $table ? [$table] : $this->db->getSchema()->getTableNames($schema);
        $viewNames = $this->db->getSchema()->getViewNames($schema);
        $tableNames = array_diff($tableNames, $viewNames);
        $command = '';

        foreach ($tableNames as $tableName) {
            $tableName = '"' . $schema . '"."' . $tableName . '"';
            $command .= "ALTER TABLE $tableName $enable TRIGGER ALL; ";
        }

        // enable to have ability to alter several tables
        $this->db->getMasterPdo()->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

        return $command;
    }

    
    public function alterColumn($table, $column, $type)
    {
        // https://github.com/aabcsoft/aabc2/issues/4492
        // http://www.postgresql.org/docs/9.1/static/sql-altertable.html
        if (!preg_match('/^(DROP|SET|RESET)\s+/i', $type)) {
            $type = 'TYPE ' . $this->getColumnType($type);
        }
        return 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' ALTER COLUMN '
            . $this->db->quoteColumnName($column) . ' ' . $type;
    }

    
    public function insert($table, $columns, &$params)
    {
        return parent::insert($table, $this->normalizeTableRowData($table, $columns), $params);
    }

    
    public function update($table, $columns, $condition, &$params)
    {
        return parent::update($table, $this->normalizeTableRowData($table, $columns), $condition, $params);
    }

    
    private function normalizeTableRowData($table, $columns)
    {
        if ($columns instanceof \aabc\db\Query) {
            return $columns;
        }

        if (($tableSchema = $this->db->getSchema()->getTableSchema($table)) !== null) {
            $columnSchemas = $tableSchema->columns;
            foreach ($columns as $name => $value) {
                if (isset($columnSchemas[$name]) && $columnSchemas[$name]->type === Schema::TYPE_BINARY && is_string($value)) {
                    $columns[$name] = [$value, \PDO::PARAM_LOB]; // explicitly setup PDO param type for binary column
                }
            }
        }

        return $columns;
    }

    
    public function batchInsert($table, $columns, $rows)
    {
        if (empty($rows)) {
            return '';
        }

        $schema = $this->db->getSchema();
        if (($tableSchema = $schema->getTableSchema($table)) !== null) {
            $columnSchemas = $tableSchema->columns;
        } else {
            $columnSchemas = [];
        }

        $values = [];
        foreach ($rows as $row) {
            $vs = [];
            foreach ($row as $i => $value) {
                if (isset($columns[$i], $columnSchemas[$columns[$i]]) && !is_array($value)) {
                    $value = $columnSchemas[$columns[$i]]->dbTypecast($value);
                }
                if (is_string($value)) {
                    $value = $schema->quoteValue($value);
                } elseif ($value === true) {
                    $value = 'TRUE';
                } elseif ($value === false) {
                    $value = 'FALSE';
                } elseif ($value === null) {
                    $value = 'NULL';
                }
                $vs[] = $value;
            }
            $values[] = '(' . implode(', ', $vs) . ')';
        }

        foreach ($columns as $i => $name) {
            $columns[$i] = $schema->quoteColumnName($name);
        }

        return 'INSERT INTO ' . $schema->quoteTableName($table)
        . ' (' . implode(', ', $columns) . ') VALUES ' . implode(', ', $values);
    }
}
