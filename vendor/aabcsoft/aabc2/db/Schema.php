<?php


namespace aabc\db;

use Aabc;
use aabc\base\Object;
use aabc\base\NotSupportedException;
use aabc\base\InvalidCallException;
use aabc\caching\Cache;
use aabc\caching\TagDependency;


abstract class Schema extends Object
{
    // The following are the supported abstract column data types.
    const TYPE_PK = 'pk';
    const TYPE_UPK = 'upk';
    const TYPE_BIGPK = 'bigpk';
    const TYPE_UBIGPK = 'ubigpk';
    const TYPE_CHAR = 'char';
    const TYPE_STRING = 'string';
    const TYPE_TEXT = 'text';
    const TYPE_SMALLINT = 'smallint';
    const TYPE_INTEGER = 'integer';
    const TYPE_BIGINT = 'bigint';
    const TYPE_FLOAT = 'float';
    const TYPE_DOUBLE = 'double';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_DATETIME = 'datetime';
    const TYPE_TIMESTAMP = 'timestamp';
    const TYPE_TIME = 'time';
    const TYPE_DATE = 'date';
    const TYPE_BINARY = 'binary';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_MONEY = 'money';

    
    public $db;
    
    public $defaultSchema;
    
    public $exceptionMap = [
        'SQLSTATE[23' => 'aabc\db\IntegrityException',
    ];
    
    public $columnSchemaClass = 'aabc\db\ColumnSchema';

    
    private $_schemaNames;
    
    private $_tableNames = [];
    
    private $_tables = [];
    
    private $_builder;


    
    protected function createColumnSchema()
    {
        return Aabc::createObject($this->columnSchemaClass);
    }

    
    abstract protected function loadTableSchema($name);

    
    public function getTableSchema($name, $refresh = false)
    {
        if (array_key_exists($name, $this->_tables) && !$refresh) {
            return $this->_tables[$name];
        }

        $db = $this->db;
        $realName = $this->getRawTableName($name);

        if ($db->enableSchemaCache && !in_array($name, $db->schemaCacheExclude, true)) {
            /* @var $cache Cache */
            $cache = is_string($db->schemaCache) ? Aabc::$app->get($db->schemaCache, false) : $db->schemaCache;
            if ($cache instanceof Cache) {
                $key = $this->getCacheKey($name);
                if ($refresh || ($table = $cache->get($key)) === false) {
                    $this->_tables[$name] = $table = $this->loadTableSchema($realName);
                    if ($table !== null) {
                        $cache->set($key, $table, $db->schemaCacheDuration, new TagDependency([
                            'tags' => $this->getCacheTag(),
                        ]));
                    }
                } else {
                    $this->_tables[$name] = $table;
                }

                return $this->_tables[$name];
            }
        }

        return $this->_tables[$name] = $this->loadTableSchema($realName);
    }

    
    protected function getCacheKey($name)
    {
        return [
            __CLASS__,
            $this->db->dsn,
            $this->db->username,
            $name,
        ];
    }

    
    protected function getCacheTag()
    {
        return md5(serialize([
            __CLASS__,
            $this->db->dsn,
            $this->db->username,
        ]));
    }

    
    public function getTableSchemas($schema = '', $refresh = false)
    {
        $tables = [];
        foreach ($this->getTableNames($schema, $refresh) as $name) {
            if ($schema !== '') {
                $name = $schema . '.' . $name;
            }
            if (($table = $this->getTableSchema($name, $refresh)) !== null) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    
    public function getSchemaNames($refresh = false)
    {
        if ($this->_schemaNames === null || $refresh) {
            $this->_schemaNames = $this->findSchemaNames();
        }

        return $this->_schemaNames;
    }

    
    public function getTableNames($schema = '', $refresh = false)
    {
        if (!isset($this->_tableNames[$schema]) || $refresh) {
            $this->_tableNames[$schema] = $this->findTableNames($schema);
        }

        return $this->_tableNames[$schema];
    }

    
    public function getQueryBuilder()
    {
        if ($this->_builder === null) {
            $this->_builder = $this->createQueryBuilder();
        }

        return $this->_builder;
    }

    
    public function getPdoType($data)
    {
        static $typeMap = [
            // php type => PDO type
            'boolean' => \PDO::PARAM_BOOL,
            'integer' => \PDO::PARAM_INT,
            'string' => \PDO::PARAM_STR,
            'resource' => \PDO::PARAM_LOB,
            'NULL' => \PDO::PARAM_NULL,
        ];
        $type = gettype($data);

        return isset($typeMap[$type]) ? $typeMap[$type] : \PDO::PARAM_STR;
    }

    
    public function refresh()
    {
        /* @var $cache Cache */
        $cache = is_string($this->db->schemaCache) ? Aabc::$app->get($this->db->schemaCache, false) : $this->db->schemaCache;
        if ($this->db->enableSchemaCache && $cache instanceof Cache) {
            TagDependency::invalidate($cache, $this->getCacheTag());
        }
        $this->_tableNames = [];
        $this->_tables = [];
    }

    
    public function refreshTableSchema($name)
    {
        unset($this->_tables[$name]);
        $this->_tableNames = [];
        /* @var $cache Cache */
        $cache = is_string($this->db->schemaCache) ? Aabc::$app->get($this->db->schemaCache, false) : $this->db->schemaCache;
        if ($this->db->enableSchemaCache && $cache instanceof Cache) {
            $cache->delete($this->getCacheKey($name));
        }
    }

    
    public function createQueryBuilder()
    {
        return new QueryBuilder($this->db);
    }

    
    public function createColumnSchemaBuilder($type, $length = null)
    {
        return new ColumnSchemaBuilder($type, $length);
    }

    
    protected function findSchemaNames()
    {
        throw new NotSupportedException(get_class($this) . ' does not support fetching all schema names.');
    }

    
    protected function findTableNames($schema = '')
    {
        throw new NotSupportedException(get_class($this) . ' does not support fetching all table names.');
    }

    
    public function findUniqueIndexes($table)
    {
        throw new NotSupportedException(get_class($this) . ' does not support getting unique indexes information.');
    }

    
    public function getLastInsertID($sequenceName = '')
    {
        if ($this->db->isActive) {
            return $this->db->pdo->lastInsertId($sequenceName === '' ? null : $this->quoteTableName($sequenceName));
        } else {
            throw new InvalidCallException('DB Connection is not active.');
        }
    }

    
    public function supportsSavepoint()
    {
        return $this->db->enableSavepoint;
    }

    
    public function createSavepoint($name)
    {
        $this->db->createCommand("SAVEPOINT $name")->execute();
    }

    
    public function releaseSavepoint($name)
    {
        $this->db->createCommand("RELEASE SAVEPOINT $name")->execute();
    }

    
    public function rollBackSavepoint($name)
    {
        $this->db->createCommand("ROLLBACK TO SAVEPOINT $name")->execute();
    }

    
    public function setTransactionIsolationLevel($level)
    {
        $this->db->createCommand("SET TRANSACTION ISOLATION LEVEL $level;")->execute();
    }

    
    public function insert($table, $columns)
    {
        $command = $this->db->createCommand()->insert($table, $columns);
        if (!$command->execute()) {
            return false;
        }
        $tableSchema = $this->getTableSchema($table);
        $result = [];
        foreach ($tableSchema->primaryKey as $name) {
            if ($tableSchema->columns[$name]->autoIncrement) {
                $result[$name] = $this->getLastInsertID($tableSchema->sequenceName);
                break;
            } else {
                $result[$name] = isset($columns[$name]) ? $columns[$name] : $tableSchema->columns[$name]->defaultValue;
            }
        }
        return $result;
    }

    
    public function quoteValue($str)
    {
        if (!is_string($str)) {
            return $str;
        }

        if (($value = $this->db->getSlavePdo()->quote($str)) !== false) {
            return $value;
        } else {
            // the driver doesn't support quote (e.g. oci)
            return "'" . addcslashes(str_replace("'", "''", $str), "\000\n\r\\\032") . "'";
        }
    }

    
    public function quoteTableName($name)
    {
        if (strpos($name, '(') !== false || strpos($name, '{{') !== false) {
            return $name;
        }
        if (strpos($name, '.') === false) {
            return $this->quoteSimpleTableName($name);
        }
        $parts = explode('.', $name);
        foreach ($parts as $i => $part) {
            $parts[$i] = $this->quoteSimpleTableName($part);
        }

        return implode('.', $parts);

    }

    
    public function quoteColumnName($name)
    {
        if (strpos($name, '(') !== false || strpos($name, '[[') !== false) {
            return $name;
        }
        if (($pos = strrpos($name, '.')) !== false) {
            $prefix = $this->quoteTableName(substr($name, 0, $pos)) . '.';
            $name = substr($name, $pos + 1);
        } else {
            $prefix = '';
        }
        if (strpos($name, '{{') !== false) {
            return $name;
        }
        return $prefix . $this->quoteSimpleColumnName($name);
    }

    
    public function quoteSimpleTableName($name)
    {
        return strpos($name, "'") !== false ? $name : "'" . $name . "'";
    }

    
    public function quoteSimpleColumnName($name)
    {
        return strpos($name, '"') !== false || $name === '*' ? $name : '"' . $name . '"';
    }

    
    public function getRawTableName($name)
    {
        if (strpos($name, '{{') !== false) {
            $name = preg_replace('/\\{\\{(.*?)\\}\\}/', '\1', $name);

            return str_replace('%', $this->db->tablePrefix, $name);
        } else {
            return $name;
        }
    }

    
    protected function getColumnPhpType($column)
    {
        static $typeMap = [
            // abstract type => php type
            'smallint' => 'integer',
            'integer' => 'integer',
            'bigint' => 'integer',
            'boolean' => 'boolean',
            'float' => 'double',
            'double' => 'double',
            'binary' => 'resource',
        ];
        if (isset($typeMap[$column->type])) {
            if ($column->type === 'bigint') {
                return PHP_INT_SIZE === 8 && !$column->unsigned ? 'integer' : 'string';
            } elseif ($column->type === 'integer') {
                return PHP_INT_SIZE === 4 && $column->unsigned ? 'string' : 'integer';
            } else {
                return $typeMap[$column->type];
            }
        } else {
            return 'string';
        }
    }

    
    public function convertException(\Exception $e, $rawSql)
    {
        if ($e instanceof Exception) {
            return $e;
        }

        $exceptionClass = '\aabc\db\Exception';
        foreach ($this->exceptionMap as $error => $class) {
            if (strpos($e->getMessage(), $error) !== false) {
                $exceptionClass = $class;
            }
        }
        $message = $e->getMessage()  . "\nThe SQL being executed was: $rawSql";
        $errorInfo = $e instanceof \PDOException ? $e->errorInfo : null;
        return new $exceptionClass($message, $errorInfo, (int) $e->getCode(), $e);
    }

    
    public function isReadQuery($sql)
    {
        $pattern = '/^\s*(SELECT|SHOW|DESCRIBE)\b/i';
        return preg_match($pattern, $sql) > 0;
    }
}
