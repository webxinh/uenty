<?php


namespace aabc\db\mysql;

use aabc\db\Expression;
use aabc\db\TableSchema;
use aabc\db\ColumnSchema;


class Schema extends \aabc\db\Schema
{
    
    public $typeMap = [
        'tinyint' => self::TYPE_SMALLINT,
        'bit' => self::TYPE_INTEGER,
        'smallint' => self::TYPE_SMALLINT,
        'mediumint' => self::TYPE_INTEGER,
        'int' => self::TYPE_INTEGER,
        'integer' => self::TYPE_INTEGER,
        'bigint' => self::TYPE_BIGINT,
        'float' => self::TYPE_FLOAT,
        'double' => self::TYPE_DOUBLE,
        'real' => self::TYPE_FLOAT,
        'decimal' => self::TYPE_DECIMAL,
        'numeric' => self::TYPE_DECIMAL,
        'tinytext' => self::TYPE_TEXT,
        'mediumtext' => self::TYPE_TEXT,
        'longtext' => self::TYPE_TEXT,
        'longblob' => self::TYPE_BINARY,
        'blob' => self::TYPE_BINARY,
        'text' => self::TYPE_TEXT,
        'varchar' => self::TYPE_STRING,
        'string' => self::TYPE_STRING,
        'char' => self::TYPE_CHAR,
        'datetime' => self::TYPE_DATETIME,
        'year' => self::TYPE_DATE,
        'date' => self::TYPE_DATE,
        'time' => self::TYPE_TIME,
        'timestamp' => self::TYPE_TIMESTAMP,
        'enum' => self::TYPE_STRING,
        'varbinary' => self::TYPE_BINARY,
    ];


    
    public function quoteSimpleTableName($name)
    {
        return strpos($name, '`') !== false ? $name : "`$name`";
    }

    
    public function quoteSimpleColumnName($name)
    {
        return strpos($name, '`') !== false || $name === '*' ? $name : "`$name`";
    }

    
    public function createQueryBuilder()
    {
        return new QueryBuilder($this->db);
    }

    
    protected function loadTableSchema($name)
    {
        $table = new TableSchema;
        $this->resolveTableNames($table, $name);

        if ($this->findColumns($table)) {
            $this->findConstraints($table);

            return $table;
        } else {
            return null;
        }
    }

    
    protected function resolveTableNames($table, $name)
    {
        $parts = explode('.', str_replace('`', '', $name));
        if (isset($parts[1])) {
            $table->schemaName = $parts[0];
            $table->name = $parts[1];
            $table->fullName = $table->schemaName . '.' . $table->name;
        } else {
            $table->fullName = $table->name = $parts[0];
        }
    }

    
    protected function loadColumnSchema($info)
    {
        $column = $this->createColumnSchema();

        $column->name = $info['field'];
        $column->allowNull = $info['null'] === 'YES';
        $column->isPrimaryKey = strpos($info['key'], 'PRI') !== false;
        $column->autoIncrement = stripos($info['extra'], 'auto_increment') !== false;
        $column->comment = $info['comment'];

        $column->dbType = $info['type'];
        $column->unsigned = stripos($column->dbType, 'unsigned') !== false;

        $column->type = self::TYPE_STRING;
        if (preg_match('/^(\w+)(?:\(([^\)]+)\))?/', $column->dbType, $matches)) {
            $type = strtolower($matches[1]);
            if (isset($this->typeMap[$type])) {
                $column->type = $this->typeMap[$type];
            }
            if (!empty($matches[2])) {
                if ($type === 'enum') {
                    preg_match_all("/'[^']*'/", $matches[2], $values);
                    foreach ($values[0] as $i => $value) {
                        $values[$i] = trim($value, "'");
                    }
                    $column->enumValues = $values;
                } else {
                    $values = explode(',', $matches[2]);
                    $column->size = $column->precision = (int) $values[0];
                    if (isset($values[1])) {
                        $column->scale = (int) $values[1];
                    }
                    if ($column->size === 1 && $type === 'bit') {
                        $column->type = 'boolean';
                    } elseif ($type === 'bit') {
                        if ($column->size > 32) {
                            $column->type = 'bigint';
                        } elseif ($column->size === 32) {
                            $column->type = 'integer';
                        }
                    }
                }
            }
        }

        $column->phpType = $this->getColumnPhpType($column);

        if (!$column->isPrimaryKey) {
            if ($column->type === 'timestamp' && $info['default'] === 'CURRENT_TIMESTAMP') {
                $column->defaultValue = new Expression('CURRENT_TIMESTAMP');
            } elseif (isset($type) && $type === 'bit') {
                $column->defaultValue = bindec(trim($info['default'], 'b\''));
            } else {
                $column->defaultValue = $column->phpTypecast($info['default']);
            }
        }

        return $column;
    }

    
    protected function findColumns($table)
    {
        $sql = 'SHOW FULL COLUMNS FROM ' . $this->quoteTableName($table->fullName);
        try {
            $columns = $this->db->createCommand($sql)->queryAll();
        } catch (\Exception $e) {
            $previous = $e->getPrevious();
            if ($previous instanceof \PDOException && strpos($previous->getMessage(), 'SQLSTATE[42S02') !== false) {
                // table does not exist
                // https://dev.mysql.com/doc/refman/5.5/en/error-messages-server.html#error_er_bad_table_error
                return false;
            }
            throw $e;
        }
        foreach ($columns as $info) {
            if ($this->db->slavePdo->getAttribute(\PDO::ATTR_CASE) !== \PDO::CASE_LOWER) {
                $info = array_change_key_case($info, CASE_LOWER);
            }
            $column = $this->loadColumnSchema($info);
            $table->columns[$column->name] = $column;
            if ($column->isPrimaryKey) {
                $table->primaryKey[] = $column->name;
                if ($column->autoIncrement) {
                    $table->sequenceName = '';
                }
            }
        }

        return true;
    }

    
    protected function getCreateTableSql($table)
    {
        $row = $this->db->createCommand('SHOW CREATE TABLE ' . $this->quoteTableName($table->fullName))->queryOne();
        if (isset($row['Create Table'])) {
            $sql = $row['Create Table'];
        } else {
            $row = array_values($row);
            $sql = $row[1];
        }

        return $sql;
    }

    
    protected function findConstraints($table)
    {
        $sql = <<<SQL
SELECT
    kcu.constraint_name,
    kcu.column_name,
    kcu.referenced_table_name,
    kcu.referenced_column_name
FROM information_schema.referential_constraints AS rc
JOIN information_schema.key_column_usage AS kcu ON
    (
        kcu.constraint_catalog = rc.constraint_catalog OR
        (kcu.constraint_catalog IS NULL AND rc.constraint_catalog IS NULL)
    ) AND
    kcu.constraint_schema = rc.constraint_schema AND
    kcu.constraint_name = rc.constraint_name
WHERE rc.constraint_schema = database() AND kcu.table_schema = database()
AND rc.table_name = :tableName AND kcu.table_name = :tableName1
SQL;

        try {
            $rows = $this->db->createCommand($sql, [':tableName' => $table->name, ':tableName1' => $table->name])->queryAll();
            $constraints = [];

            foreach ($rows as $row) {
                $constraints[$row['constraint_name']]['referenced_table_name'] = $row['referenced_table_name'];
                $constraints[$row['constraint_name']]['columns'][$row['column_name']] = $row['referenced_column_name'];
            }

            $table->foreignKeys = [];
            foreach ($constraints as $name => $constraint) {
                $table->foreignKeys[$name] = array_merge(
                    [$constraint['referenced_table_name']],
                    $constraint['columns']
                );
            }
        } catch (\Exception $e) {
            $previous = $e->getPrevious();
            if (!$previous instanceof \PDOException || strpos($previous->getMessage(), 'SQLSTATE[42S02') === false) {
                throw $e;
            }

            // table does not exist, try to determine the foreign keys using the table creation sql
            $sql = $this->getCreateTableSql($table);
            $regexp = '/FOREIGN KEY\s+\(([^\)]+)\)\s+REFERENCES\s+([^\(^\s]+)\s*\(([^\)]+)\)/mi';
            if (preg_match_all($regexp, $sql, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $fks = array_map('trim', explode(',', str_replace('`', '', $match[1])));
                    $pks = array_map('trim', explode(',', str_replace('`', '', $match[3])));
                    $constraint = [str_replace('`', '', $match[2])];
                    foreach ($fks as $k => $name) {
                        $constraint[$name] = $pks[$k];
                    }
                    $table->foreignKeys[md5(serialize($constraint))] = $constraint;
                }
                $table->foreignKeys = array_values($table->foreignKeys);
            }
        }
    }

    
    public function findUniqueIndexes($table)
    {
        $sql = $this->getCreateTableSql($table);
        $uniqueIndexes = [];

        $regexp = '/UNIQUE KEY\s+([^\(\s]+)\s*\(([^\(\)]+)\)/mi';
        if (preg_match_all($regexp, $sql, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $indexName = str_replace('`', '', $match[1]);
                $indexColumns = array_map('trim', explode(',', str_replace('`', '', $match[2])));
                $uniqueIndexes[$indexName] = $indexColumns;
            }
        }

        return $uniqueIndexes;
    }

    
    protected function findTableNames($schema = '')
    {
        $sql = 'SHOW TABLES';
        if ($schema !== '') {
            $sql .= ' FROM ' . $this->quoteSimpleTableName($schema);
        }

        return $this->db->createCommand($sql)->queryColumn();
    }

    
    public function createColumnSchemaBuilder($type, $length = null)
    {
        return new ColumnSchemaBuilder($type, $length, $this->db);
    }
}
