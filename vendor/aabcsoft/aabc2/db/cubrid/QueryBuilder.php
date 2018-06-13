<?php


namespace aabc\db\cubrid;

use aabc\base\InvalidParamException;
use aabc\db\Exception;


class QueryBuilder extends \aabc\db\QueryBuilder
{
    
    public $typeMap = [
        Schema::TYPE_PK => 'int NOT NULL AUTO_INCREMENT PRIMARY KEY',
        Schema::TYPE_UPK => 'int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
        Schema::TYPE_BIGPK => 'bigint NOT NULL AUTO_INCREMENT PRIMARY KEY',
        Schema::TYPE_UBIGPK => 'bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
        Schema::TYPE_CHAR => 'char(1)',
        Schema::TYPE_STRING => 'varchar(255)',
        Schema::TYPE_TEXT => 'varchar',
        Schema::TYPE_SMALLINT => 'smallint',
        Schema::TYPE_INTEGER => 'int',
        Schema::TYPE_BIGINT => 'bigint',
        Schema::TYPE_FLOAT => 'float(7)',
        Schema::TYPE_DOUBLE => 'double(15)',
        Schema::TYPE_DECIMAL => 'decimal(10,0)',
        Schema::TYPE_DATETIME => 'datetime',
        Schema::TYPE_TIMESTAMP => 'timestamp',
        Schema::TYPE_TIME => 'time',
        Schema::TYPE_DATE => 'date',
        Schema::TYPE_BINARY => 'blob',
        Schema::TYPE_BOOLEAN => 'smallint',
        Schema::TYPE_MONEY => 'decimal(19,4)',
    ];


    
    public function resetSequence($tableName, $value = null)
    {
        $table = $this->db->getTableSchema($tableName);
        if ($table !== null && $table->sequenceName !== null) {
            $tableName = $this->db->quoteTableName($tableName);
            if ($value === null) {
                $key = reset($table->primaryKey);
                $value = (int) $this->db->createCommand("SELECT MAX(`$key`) FROM " . $this->db->schema->quoteTableName($tableName))->queryScalar() + 1;
            } else {
                $value = (int) $value;
            }

            return 'ALTER TABLE ' . $this->db->schema->quoteTableName($tableName) . " AUTO_INCREMENT=$value;";
        } elseif ($table === null) {
            throw new InvalidParamException("Table not found: $tableName");
        } else {
            throw new InvalidParamException("There is not sequence associated with table '$tableName'.");
        }
    }

    
    public function buildLimit($limit, $offset)
    {
        $sql = '';
        // limit is not optional in CUBRID
        // http://www.cubrid.org/manual/90/en/LIMIT%20Clause
        // "You can specify a very big integer for row_count to display to the last row, starting from a specific row."
        if ($this->hasLimit($limit)) {
            $sql = 'LIMIT ' . $limit;
            if ($this->hasOffset($offset)) {
                $sql .= ' OFFSET ' . $offset;
            }
        } elseif ($this->hasOffset($offset)) {
            $sql = "LIMIT 9223372036854775807 OFFSET $offset"; // 2^63-1
        }

        return $sql;
    }

    
    public function selectExists($rawSql)
    {
        return 'SELECT CASE WHEN EXISTS(' . $rawSql . ') THEN 1 ELSE 0 END';
    }

    
    public function addCommentOnColumn($table, $column, $comment)
    {
        $definition = $this->getColumnDefinition($table, $column);
        $definition = trim(preg_replace("/COMMENT '(.*?)'/i", '', $definition));

        return 'ALTER TABLE ' . $this->db->quoteTableName($table)
        . ' CHANGE ' . $this->db->quoteColumnName($column)
        . ' ' . $this->db->quoteColumnName($column)
        . (empty($definition) ? '' : ' ' . $definition)
        . ' COMMENT ' . $this->db->quoteValue($comment);
    }

    
    public function addCommentOnTable($table, $comment)
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($table) . ' COMMENT ' . $this->db->quoteValue($comment);
    }

    
    public function dropCommentFromColumn($table, $column)
    {
        return $this->addCommentOnColumn($table, $column, '');
    }

    
    public function dropCommentFromTable($table)
    {
        return $this->addCommentOnTable($table, '');
    }


    
    private function getColumnDefinition($table, $column)
    {
        $row = $this->db->createCommand('SHOW CREATE TABLE ' . $this->db->quoteTableName($table))->queryOne();
        if ($row === false) {
            throw new Exception("Unable to find column '$column' in table '$table'.");
        }
        if (isset($row['Create Table'])) {
            $sql = $row['Create Table'];
        } else {
            $row = array_values($row);
            $sql = $row[1];
        }
        $sql = preg_replace('/^[^(]+\((.*)\).*$/', '\1', $sql);
        $sql = str_replace(', [', ",\n[", $sql);
        if (preg_match_all('/^\s*\[(.*?)\]\s+(.*?),?$/m', $sql, $matches)) {
            foreach ($matches[1] as $i => $c) {
                if ($c === $column) {
                    return $matches[2][$i];
                }
            }
        }
        return null;
    }
}
