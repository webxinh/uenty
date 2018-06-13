<?php
namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;
use Codeception\Configuration;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleConfigException;
use Codeception\Lib\Interfaces\Db as DbInterface;
use Codeception\Lib\Driver\Db as Driver;
use Codeception\TestInterface;


class Db extends CodeceptionModule implements DbInterface
{
    
    public $dbh;

    
    protected $sql = [];

    
    protected $config = [
        'populate' => true,
        'cleanup' => true,
        'reconnect' => false,
        'dump' => null
    ];

    
    protected $populated = false;

    
    public $driver;

    
    protected $insertedRows = [];

    
    protected $requiredFields = ['dsn', 'user', 'password'];

    public function _initialize()
    {
        if ($this->config['dump'] && ($this->config['cleanup'] or ($this->config['populate']))) {
            $this->readSql();
        }

        $this->connect();

        // starting with loading dump
        if ($this->config['populate']) {
            if ($this->config['cleanup']) {
                $this->cleanup();
            }
            $this->loadDump();
            $this->populated = true;
        }

        if ($this->config['reconnect']) {
            $this->disconnect();
        }
    }

    private function readSql()
    {
        if (!file_exists(Configuration::projectDir() . $this->config['dump'])) {
            throw new ModuleConfigException(
                __CLASS__,
                "\nFile with dump doesn't exist.\n"
                . "Please, check path for sql file: "
                . $this->config['dump']
            );
        }

        $sql = file_get_contents(Configuration::projectDir() . $this->config['dump']);

        // remove C-style comments (except MySQL directives)
        $sql = preg_replace('%/\*(?!!\d+).*?\*/%s', '', $sql);

        if (!empty($sql)) {
            // split SQL dump into lines
            $this->sql = preg_split('/\r\n|\n|\r/', $sql, -1, PREG_SPLIT_NO_EMPTY);
        }
    }

    private function connect()
    {
        try {
            $this->driver = Driver::create($this->config['dsn'], $this->config['user'], $this->config['password']);
        } catch (\PDOException $e) {
            $message = $e->getMessage();
            if ($message === 'could not find driver') {
                list ($missingDriver, ) = explode(':', $this->config['dsn'], 2);
                $message = "could not find $missingDriver driver";
            }

            throw new ModuleException(__CLASS__, $message . ' while creating PDO connection');
        }

        $this->dbh = $this->driver->getDbh();
    }

    private function disconnect()
    {
        $this->dbh = null;
        $this->driver = null;
    }

    public function _before(TestInterface $test)
    {
        if ($this->config['reconnect']) {
            $this->connect();
        }
        if ($this->config['cleanup'] && !$this->populated) {
            $this->cleanup();
            $this->loadDump();
        }
        parent::_before($test);
    }

    public function _after(TestInterface $test)
    {
        $this->populated = false;
        $this->removeInserted();
        if ($this->config['reconnect']) {
            $this->disconnect();
        }
        parent::_after($test);
    }

    protected function removeInserted()
    {
        foreach (array_reverse($this->insertedRows) as $row) {
            try {
                $this->driver->deleteQueryByCriteria($row['table'], $row['primary']);
            } catch (\Exception $e) {
                $this->debug("couldn't delete record " . json_encode($row['primary']) ." from {$row['table']}");
            }
        }
        $this->insertedRows = [];
    }

    protected function cleanup()
    {
        $dbh = $this->driver->getDbh();
        if (!$dbh) {
            throw new ModuleConfigException(
                __CLASS__,
                'No connection to database. Remove this module from config if you don\'t need database repopulation'
            );
        }
        try {
            // don't clear database for empty dump
            if (!count($this->sql)) {
                return;
            }
            $this->driver->cleanup();
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
    }

    protected function loadDump()
    {
        if (!$this->sql) {
            return;
        }
        try {
            $this->driver->load($this->sql);
        } catch (\PDOException $e) {
            throw new ModuleException(
                __CLASS__,
                $e->getMessage() . "\nSQL query being executed: " . $this->driver->sqlToRun
            );
        }
    }

    
    public function haveInDatabase($table, array $data)
    {
        $query = $this->driver->insert($table, $data);
        $parameters = array_values($data);
        $this->debugSection('Query', $query);
        $this->debugSection('Parameters', $parameters);
        $this->driver->executeQuery($query, $parameters);

        try {
            $lastInsertId = (int)$this->driver->lastInsertId($table);
        } catch (\PDOException $e) {
            // ignore errors due to uncommon DB structure,
            // such as tables without _id_seq in PGSQL
            $lastInsertId = 0;
        }

        $this->addInsertedRow($table, $data, $lastInsertId);

        return $lastInsertId;
    }

    private function addInsertedRow($table, array $row, $id)
    {
        $primaryKey = $this->driver->getPrimaryKey($table);
        $primary = [];
        if ($primaryKey) {
            if ($id && count($primaryKey) === 1) {
                $primary [$primaryKey[0]] = $id;
            } else {
                foreach ($primaryKey as $column) {
                    if (isset($row[$column])) {
                        $primary[$column] = $row[$column];
                    } else {
                        throw new \InvalidArgumentException(
                            'Primary key field ' . $column . ' is not set for table ' . $table
                        );
                    }
                }
            }
        } else {
            $primary = $row;
        }

        $this->insertedRows[] = [
            'table' => $table,
            'primary' => $primary,
        ];
    }

    public function seeInDatabase($table, $criteria = [])
    {
        $res = $this->countInDatabase($table, $criteria);
        $this->assertGreaterThan(
            0,
            $res,
            'No matching records found for criteria ' . json_encode($criteria) . ' in table ' . $table
        );
    }

    
    public function seeNumRecords($expectedNumber, $table, array $criteria = [])
    {
        $actualNumber = $this->countInDatabase($table, $criteria);
        $this->assertEquals(
            $expectedNumber,
            $actualNumber,
            sprintf(
                'The number of found rows (%d) does not match expected number %d for criteria %s in table %s',
                $actualNumber,
                $expectedNumber,
                json_encode($criteria),
                $table
            )
        );
    }

    public function dontSeeInDatabase($table, $criteria = [])
    {
        $count = $this->countInDatabase($table, $criteria);
        $this->assertLessThan(
            1,
            $count,
            'Unexpectedly found matching records for criteria ' . json_encode($criteria) . ' in table ' . $table
        );
    }

    
    protected function countInDatabase($table, array $criteria = [])
    {
        return (int) $this->proceedSeeInDatabase($table, 'count(*)', $criteria);
    }

    protected function proceedSeeInDatabase($table, $column, $criteria)
    {
        $query = $this->driver->select($column, $table, $criteria);
        $parameters = array_values($criteria);
        $this->debugSection('Query', $query);
        if (!empty($parameters)) {
            $this->debugSection('Parameters', $parameters);
        }
        $sth = $this->driver->executeQuery($query, $parameters);

        return $sth->fetchColumn();
    }

    public function grabFromDatabase($table, $column, $criteria = [])
    {
        return $this->proceedSeeInDatabase($table, $column, $criteria);
    }

    
    public function grabNumRecords($table, array $criteria = [])
    {
        return $this->countInDatabase($table, $criteria);
    }
}
