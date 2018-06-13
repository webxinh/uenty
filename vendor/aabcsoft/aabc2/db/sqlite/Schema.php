<?php


namespace aabc\db\sqlite;

use aabc\base\NotSupportedException;
use aabc\db\Expression;
use aabc\db\TableSchema;
use aabc\db\ColumnSchema;
use aabc\db\Transaction;


class Schema extends \aabc\db\Schema
{
    
    public $typeMap = [
        'tinyint' => self::TYPE_SMALLINT,
        'bit' => self::TYPE_SMALLINT,
        'boolean' => self::TYPE_BOOLEAN,
        'bool' => self::TYPE_BOOLEAN,
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
        'text' => self::TYPE_TEXT,
        'varchar' => self::TYPE_STRING,
        'string' => self::TYPE_STRING,
        'char' => self::TYPE_CHAR,
        'blob' => self::TYPE_BINARY,
        'datetime' => self::TYPE_DATETIME,
        'year' => self::TYPE_DATE,
        'date' => self::TYPE_DATE,
        'time' => self::TYPE_TIME,
        'timestamp' => self::TYPE_TIMESTAMP,
        'enum' => self::TYPE_STRING,
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

    
    public function createColumnSchemaBuilder($type, $length = null)
    {
        return new ColumnSchemaBuilder($type, $length);
    }

    
    protected function findTableNames($schema = '')
    {
        $sql = "SELECT DISTINCT tbl_name FROM sqlite_master WHERE tbl_name<>'sqlite_sequence' ORDER BY tbl_name";

        return $this->db->createCommand($sql)->queryColumn();
    }

    
    protected function loadTableSchema($name)
    {
        $table = new TableSchema;
        $table->name = $name;
        $table->fullName = $name;

        if ($this->findColumns($table)) {
            $this->findConstraints($table);

            return $table;
        } else {
            return null;
        }
    }

    
    protected function findColumns($table)
    {
        $sql = 'PRAGMA table_info(' . $this->quoteSimpleTableName($table->name) . ')';
        $columns = $this->db->createCommand($sql)->queryAll();
        if (empty($columns)) {
            return false;
        }

        foreach ($columns as $info) {
            $column = $this->loadColumnSchema($info);
            $table->columns[$column->name] = $column;
            if ($column->isPrimaryKey) {
                $table->primaryKey[] = $column->name;
            }
        }
        if (count($table->primaryKey) === 1 && !strncasecmp($table->columns[$table->primaryKey[0]]->dbType, 'int', 3)) {
            $table->sequenceName = '';
            $table->columns[$table->primaryKey[0]]->autoIncrement = true;
        }

        return true;
    }

    
    protected function findConstraints($table)
    {
        $sql = 'PRAGMA foreign_key_list(' . $this->quoteSimpleTableName($table->name) . ')';
        $keys = $this->db->createCommand($sql)->queryAll();
        foreach ($keys as $key) {
            $id = (int) $key['id'];
            if (!isset($table->foreignKeys[$id])) {
                $table->foreignKeys[$id] = [$key['table'], $key['from'] => $key['to']];
            } else {
                // composite FK
                $table->foreignKeys[$id][$key['from']] = $key['to'];
            }
        }
    }

    
    public function findUniqueIndexes($table)
    {
        $sql = 'PRAGMA index_list(' . $this->quoteSimpleTableName($table->name) . ')';
        $indexes = $this->db->createCommand($sql)->queryAll();
        $uniqueIndexes = [];

        foreach ($indexes as $index) {
            $indexName = $index['name'];
            $indexInfo = $this->db->createCommand('PRAGMA index_info(' . $this->quoteValue($index['name']) . ')')->queryAll();

            if ($index['unique']) {
                $uniqueIndexes[$indexName] = [];
                foreach ($indexInfo as $row) {
                    $uniqueIndexes[$indexName][] = $row['name'];
                }
            }
        }

        return $uniqueIndexes;
    }

    
    protected function loadColumnSchema($info)
    {
        $column = $this->createColumnSchema();
        $column->name = $info['name'];
        $column->allowNull = !$info['notnull'];
        $column->isPrimaryKey = $info['pk'] != 0;

        $column->dbType = strtolower($info['type']);
        $column->unsigned = strpos($column->dbType, 'unsigned') !== false;

        $column->type = self::TYPE_STRING;
        if (preg_match('/^(\w+)(?:\(([^\)]+)\))?/', $column->dbType, $matches)) {
            $type = strtolower($matches[1]);
            if (isset($this->typeMap[$type])) {
                $column->type = $this->typeMap[$type];
            }

            if (!empty($matches[2])) {
                $values = explode(',', $matches[2]);
                $column->size = $column->precision = (int) $values[0];
                if (isset($values[1])) {
                    $column->scale = (int) $values[1];
                }
                if ($column->size === 1 && ($type === 'tinyint' || $type === 'bit')) {
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
        $column->phpType = $this->getColumnPhpType($column);

        if (!$column->isPrimaryKey) {
            if ($info['dflt_value'] === 'null' || $info['dflt_value'] === '' || $info['dflt_value'] === null) {
                $column->defaultValue = null;
            } elseif ($column->type === 'timestamp' && $info['dflt_value'] === 'CURRENT_TIMESTAMP') {
                $column->defaultValue = new Expression('CURRENT_TIMESTAMP');
            } else {
                $value = trim($info['dflt_value'], "'\"");
                $column->defaultValue = $column->phpTypecast($value);
            }
        }

        return $column;
    }

    
    public function setTransactionIsolationLevel($level)
    {
        switch ($level) {
            case Transaction::SERIALIZABLE:
                $this->db->createCommand('PRAGMA read_uncommitted = False;')->execute();
                break;
            case Transaction::READ_UNCOMMITTED:
                $this->db->createCommand('PRAGMA read_uncommitted = True;')->execute();
                break;
            default:
                throw new NotSupportedException(get_class($this) . ' only supports transaction isolation levels READ UNCOMMITTED and SERIALIZABLE.');
        }
    }
}
