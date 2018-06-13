<?php


namespace aabc\db;

use PDO;
use Aabc;
use aabc\base\Component;
use aabc\base\InvalidConfigException;
use aabc\base\NotSupportedException;
use aabc\caching\Cache;


class Connection extends Component
{
    
    const EVENT_AFTER_OPEN = 'afterOpen';
    
    const EVENT_BEGIN_TRANSACTION = 'beginTransaction';
    
    const EVENT_COMMIT_TRANSACTION = 'commitTransaction';
    
    const EVENT_ROLLBACK_TRANSACTION = 'rollbackTransaction';

    
    public $dsn;
    
    public $username;
    
    public $password;
    
    public $attributes;
    
    public $pdo;
    
    public $enableSchemaCache = false;
    
    public $schemaCacheDuration = 3600;
    
    public $schemaCacheExclude = [];
    
    public $schemaCache = 'cache';
    
    public $enableQueryCache = true;
    
    public $queryCacheDuration = 3600;
    
    public $queryCache = 'cache';
    
    public $charset;
    
    public $emulatePrepare;
    
    public $tablePrefix = '';
    
    public $schemaMap = [
        'pgsql' => 'aabc\db\pgsql\Schema', // PostgreSQL
        'mysqli' => 'aabc\db\mysql\Schema', // MySQL
        'mysql' => 'aabc\db\mysql\Schema', // MySQL
        'sqlite' => 'aabc\db\sqlite\Schema', // sqlite 3
        'sqlite2' => 'aabc\db\sqlite\Schema', // sqlite 2
        'sqlsrv' => 'aabc\db\mssql\Schema', // newer MSSQL driver on MS Windows hosts
        'oci' => 'aabc\db\oci\Schema', // Oracle driver
        'mssql' => 'aabc\db\mssql\Schema', // older MSSQL driver on MS Windows hosts
        'dblib' => 'aabc\db\mssql\Schema', // dblib drivers on GNU/Linux (and maybe other OSes) hosts
        'cubrid' => 'aabc\db\cubrid\Schema', // CUBRID
    ];
    
    public $pdoClass;
    
    public $commandClass = 'aabc\db\Command';
    
    public $enableSavepoint = true;
    
    public $serverStatusCache = 'cache';
    
    public $serverRetryInterval = 600;
    
    public $enableSlaves = true;
    
    public $slaves = [];
    
    public $slaveConfig = [];
    
    public $masters = [];
    
    public $masterConfig = [];
    
    public $shuffleMasters = true;

    
    private $_transaction;
    
    private $_schema;
    
    private $_driverName;
    
    private $_master = false;
    
    private $_slave = false;
    
    private $_queryCacheInfo = [];


    
    public function getIsActive()
    {
        return $this->pdo !== null;
    }

    
    public function cache(callable $callable, $duration = null, $dependency = null)
    {
        $this->_queryCacheInfo[] = [$duration === null ? $this->queryCacheDuration : $duration, $dependency];
        try {
            $result = call_user_func($callable, $this);
            array_pop($this->_queryCacheInfo);
            return $result;
        } catch (\Exception $e) {
            array_pop($this->_queryCacheInfo);
            throw $e;
        } catch (\Throwable $e) {
            array_pop($this->_queryCacheInfo);
            throw $e;
        }
    }

    
    public function noCache(callable $callable)
    {
        $this->_queryCacheInfo[] = false;
        try {
            $result = call_user_func($callable, $this);
            array_pop($this->_queryCacheInfo);
            return $result;
        } catch (\Exception $e) {
            array_pop($this->_queryCacheInfo);
            throw $e;
        } catch (\Throwable $e) {
            array_pop($this->_queryCacheInfo);
            throw $e;
        }
    }

    
    public function getQueryCacheInfo($duration, $dependency)
    {
        if (!$this->enableQueryCache) {
            return null;
        }

        $info = end($this->_queryCacheInfo);
        if (is_array($info)) {
            if ($duration === null) {
                $duration = $info[0];
            }
            if ($dependency === null) {
                $dependency = $info[1];
            }
        }

        if ($duration === 0 || $duration > 0) {
            if (is_string($this->queryCache) && Aabc::$app) {
                $cache = Aabc::$app->get($this->queryCache, false);
            } else {
                $cache = $this->queryCache;
            }
            if ($cache instanceof Cache) {
                return [$cache, $duration, $dependency];
            }
        }

        return null;
    }

    
    public function open()
    {
        if ($this->pdo !== null) {
            return;
        }

        if (!empty($this->masters)) {
            $db = $this->getMaster();
            if ($db !== null) {
                $this->pdo = $db->pdo;
                return;
            } else {
                throw new InvalidConfigException('None of the master DB servers is available.');
            }
        }

        if (empty($this->dsn)) {
            throw new InvalidConfigException('Connection::dsn cannot be empty.');
        }
        $token = 'Opening DB connection: ' . $this->dsn;
        try {
            Aabc::info($token, __METHOD__);
            Aabc::beginProfile($token, __METHOD__);
            $this->pdo = $this->createPdoInstance();
            $this->initConnection();
            Aabc::endProfile($token, __METHOD__);
        } catch (\PDOException $e) {
            Aabc::endProfile($token, __METHOD__);
            throw new Exception($e->getMessage(), $e->errorInfo, (int) $e->getCode(), $e);
        }
    }

    
    public function close()
    {
        if ($this->_master) {
            if ($this->pdo === $this->_master->pdo) {
                $this->pdo = null;
            }

            $this->_master->close();
            $this->_master = null;
        }

        if ($this->pdo !== null) {
            Aabc::trace('Closing DB connection: ' . $this->dsn, __METHOD__);
            $this->pdo = null;
            $this->_schema = null;
            $this->_transaction = null;
        }

        if ($this->_slave) {
            $this->_slave->close();
            $this->_slave = null;
        }
    }

    
    protected function createPdoInstance()
    {
        $pdoClass = $this->pdoClass;
        if ($pdoClass === null) {
            $pdoClass = 'PDO';
            if ($this->_driverName !== null) {
                $driver = $this->_driverName;
            } elseif (($pos = strpos($this->dsn, ':')) !== false) {
                $driver = strtolower(substr($this->dsn, 0, $pos));
            }
            if (isset($driver)) {
                if ($driver === 'mssql' || $driver === 'dblib') {
                    $pdoClass = 'aabc\db\mssql\PDO';
                } elseif ($driver === 'sqlsrv') {
                    $pdoClass = 'aabc\db\mssql\SqlsrvPDO';
                }
            }
        }

        $dsn = $this->dsn;
        if (strncmp('sqlite:@', $dsn, 8) === 0) {
            $dsn = 'sqlite:' . Aabc::getAlias(substr($dsn, 7));
        }
        return new $pdoClass($dsn, $this->username, $this->password, $this->attributes);
    }

    
    protected function initConnection()
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if ($this->emulatePrepare !== null && constant('PDO::ATTR_EMULATE_PREPARES')) {
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, $this->emulatePrepare);
        }
        if ($this->charset !== null && in_array($this->getDriverName(), ['pgsql', 'mysql', 'mysqli', 'cubrid'], true)) {
            $this->pdo->exec('SET NAMES ' . $this->pdo->quote($this->charset));
        }
        $this->trigger(self::EVENT_AFTER_OPEN);
    }

    
    public function createCommand($sql = null, $params = [])
    {
        
        $command = new $this->commandClass([
            'db' => $this,
            'sql' => $sql,
        ]);

        return $command->bindValues($params);
    }

    
    public function getTransaction()
    {
        return $this->_transaction && $this->_transaction->getIsActive() ? $this->_transaction : null;
    }

    
    public function beginTransaction($isolationLevel = null)
    {
        $this->open();

        if (($transaction = $this->getTransaction()) === null) {
            $transaction = $this->_transaction = new Transaction(['db' => $this]);
        }
        $transaction->begin($isolationLevel);

        return $transaction;
    }

    
    public function transaction(callable $callback, $isolationLevel = null)
    {
        $transaction = $this->beginTransaction($isolationLevel);
        $level = $transaction->level;

        try {
            $result = call_user_func($callback, $this);
            if ($transaction->isActive && $transaction->level === $level) {
                $transaction->commit();
            }
        } catch (\Exception $e) {
            if ($transaction->isActive && $transaction->level === $level) {
                $transaction->rollBack();
            }
            throw $e;
        } catch (\Throwable $e) {
            if ($transaction->isActive && $transaction->level === $level) {
                $transaction->rollBack();
            }
            throw $e;
        }

        return $result;
    }

    
    public function getSchema()
    {
        if ($this->_schema !== null) {
            return $this->_schema;
        } else {
            $driver = $this->getDriverName();
            if (isset($this->schemaMap[$driver])) {
                $config = !is_array($this->schemaMap[$driver]) ? ['class' => $this->schemaMap[$driver]] : $this->schemaMap[$driver];
                $config['db'] = $this;

                return $this->_schema = Aabc::createObject($config);
            } else {
                throw new NotSupportedException("Connection does not support reading schema information for '$driver' DBMS.");
            }
        }
    }

    
    public function getQueryBuilder()
    {
        return $this->getSchema()->getQueryBuilder();
    }

    
    public function getTableSchema($name, $refresh = false)
    {
        return $this->getSchema()->getTableSchema($name, $refresh);
    }

    
    public function getLastInsertID($sequenceName = '')
    {
        return $this->getSchema()->getLastInsertID($sequenceName);
    }

    
    public function quoteValue($value)
    {
        return $this->getSchema()->quoteValue($value);
    }

    
    public function quoteTableName($name)
    {
        return $this->getSchema()->quoteTableName($name);
    }

    
    public function quoteColumnName($name)
    {
        return $this->getSchema()->quoteColumnName($name);
    }

    
    public function quoteSql($sql)
    {
        return preg_replace_callback(
            '/(\\{\\{(%?[\w\-\. ]+%?)\\}\\}|\\[\\[([\w\-\. ]+)\\]\\])/',
            function ($matches) {
                if (isset($matches[3])) {
                    return $this->quoteColumnName($matches[3]);
                } else {
                    return str_replace('%', $this->tablePrefix, $this->quoteTableName($matches[2]));
                }
            },
            $sql
        );
    }

    
    public function getDriverName()
    {
        if ($this->_driverName === null) {
            if (($pos = strpos($this->dsn, ':')) !== false) {
                $this->_driverName = strtolower(substr($this->dsn, 0, $pos));
            } else {
                $this->_driverName = strtolower($this->getSlavePdo()->getAttribute(PDO::ATTR_DRIVER_NAME));
            }
        }
        return $this->_driverName;
    }

    
    public function setDriverName($driverName)
    {
        $this->_driverName = strtolower($driverName);
    }

    
    public function getSlavePdo($fallbackToMaster = true)
    {
        $db = $this->getSlave(false);
        if ($db === null) {
            return $fallbackToMaster ? $this->getMasterPdo() : null;
        } else {
            return $db->pdo;
        }
    }

    
    public function getMasterPdo()
    {
        $this->open();
        return $this->pdo;
    }

    
    public function getSlave($fallbackToMaster = true)
    {
        if (!$this->enableSlaves) {
            return $fallbackToMaster ? $this : null;
        }

        if ($this->_slave === false) {
            $this->_slave = $this->openFromPool($this->slaves, $this->slaveConfig);
        }

        return $this->_slave === null && $fallbackToMaster ? $this : $this->_slave;
    }

    
    public function getMaster()
    {
        if ($this->_master === false) {
            $this->_master = ($this->shuffleMasters)
                ? $this->openFromPool($this->masters, $this->masterConfig)
                : $this->openFromPoolSequentially($this->masters, $this->masterConfig);
        }

        return $this->_master;
    }

    
    public function useMaster(callable $callback)
    {
        $enableSlave = $this->enableSlaves;
        $this->enableSlaves = false;
        $result = call_user_func($callback, $this);
        $this->enableSlaves = $enableSlave;
        return $result;
    }

    
    protected function openFromPool(array $pool, array $sharedConfig)
    {
        shuffle($pool);
        return $this->openFromPoolSequentially($pool, $sharedConfig);
    }

    
    protected function openFromPoolSequentially(array $pool, array $sharedConfig)
    {
        if (empty($pool)) {
            return null;
        }

        if (!isset($sharedConfig['class'])) {
            $sharedConfig['class'] = get_class($this);
        }

        $cache = is_string($this->serverStatusCache) ? Aabc::$app->get($this->serverStatusCache, false) : $this->serverStatusCache;

        foreach ($pool as $config) {
            $config = array_merge($sharedConfig, $config);
            if (empty($config['dsn'])) {
                throw new InvalidConfigException('The "dsn" option must be specified.');
            }

            $key = [__METHOD__, $config['dsn']];
            if ($cache instanceof Cache && $cache->get($key)) {
                // should not try this dead server now
                continue;
            }

            /* @var $db Connection */
            $db = Aabc::createObject($config);

            try {
                $db->open();
                return $db;
            } catch (\Exception $e) {
                Aabc::warning("Connection ({$config['dsn']}) failed: " . $e->getMessage(), __METHOD__);
                if ($cache instanceof Cache) {
                    // mark this server as dead and only retry it after the specified interval
                    $cache->set($key, 1, $this->serverRetryInterval);
                }
            }
        }

        return null;
    }

    
    public function __sleep()
    {
        $this->close();
        return array_keys((array) $this);
    }
}
