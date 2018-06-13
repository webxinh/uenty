<?php
namespace Codeception\Module;

use Codeception\Lib\Interfaces\RequiresPackage;
use Codeception\Module as CodeceptionModule;
use Codeception\Configuration as Configuration;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Driver\MongoDb as MongoDbDriver;
use Codeception\TestInterface;


class MongoDb extends CodeceptionModule implements RequiresPackage
{
    const DUMP_TYPE_JS = 'js';
    const DUMP_TYPE_MONGODUMP = 'mongodump';
    const DUMP_TYPE_MONGODUMP_TAR_GZ = 'mongodump-tar-gz';

    
    public $dbh;

    

    protected $dumpFile;
    protected $isDumpFileEmpty = true;

    protected $config = [
        'populate'  => true,
        'cleanup'   => true,
        'dump'      => null,
        'dump_type' => self::DUMP_TYPE_JS,
        'user'      => null,
        'password'  => null,
        'quiet'     => false,
    ];

    protected $populated = false;

    
    public $driver;

    protected $requiredFields = ['dsn'];

    public function _initialize()
    {

        try {
            $this->driver = MongoDbDriver::create(
                $this->config['dsn'],
                $this->config['user'],
                $this->config['password']
            );
        } catch (\MongoConnectionException $e) {
            throw new ModuleException(__CLASS__, $e->getMessage() . ' while creating Mongo connection');
        }

        // starting with loading dump
        if ($this->config['populate']) {
            $this->cleanup();
            $this->loadDump();
            $this->populated = true;
        }
    }

    private function validateDump()
    {
        if ($this->config['dump'] && ($this->config['cleanup'] or ($this->config['populate']))) {
            if (!file_exists(Configuration::projectDir() . $this->config['dump'])) {
                throw new ModuleConfigException(
                    __CLASS__,
                    "File with dump doesn't exist.\n
                    Please, check path for dump file: " . $this->config['dump']
                );
            }
            $this->dumpFile = Configuration::projectDir() . $this->config['dump'];
            $this->isDumpFileEmpty = false;

            if ($this->config['dump_type'] === self::DUMP_TYPE_JS) {
                $content = file_get_contents($this->dumpFile);
                $content = trim(preg_replace('%/\*(?:(?!\*/).)*\*/%s', "", $content));
                if (!sizeof(explode("\n", $content))) {
                    $this->isDumpFileEmpty = true;
                }
                return;
            }

            if ($this->config['dump_type'] === self::DUMP_TYPE_MONGODUMP) {
                if (!is_dir($this->dumpFile)) {
                    throw new ModuleConfigException(
                        __CLASS__,
                        "Dump must be a directory.\n
                        Please, check dump: " . $this->config['dump']
                    );
                }
                $this->isDumpFileEmpty = true;
                $dumpDir = dir($this->dumpFile);
                while (false !== ($entry = $dumpDir->read())) {
                    if ($entry !== '..' && $entry !== '.') {
                        $this->isDumpFileEmpty = false;
                        break;
                    }
                }
                $dumpDir->close();
                return;
            }

            if ($this->config['dump_type'] === self::DUMP_TYPE_MONGODUMP_TAR_GZ) {
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    throw new ModuleConfigException(
                        __CLASS__,
                        "Tar gunzip archives are not supported for Windows systems"
                    );
                }
                if (strlen($this->dumpFile) <= 7 || substr($this->dumpFile, -7) !== '.tar.gz') {
                    throw new ModuleConfigException(
                        __CLASS__,
                        "Dump file must be a valid tar gunzip archive.\n
                        Please, check dump file: " . $this->config['dump']
                    );
                }
                return;
            }

            throw new ModuleConfigException(
                __CLASS__,
                '\"dump_type\" must be one of ["'
                . self::DUMP_TYPE_JS . '", "'
                . self::DUMP_TYPE_MONGODUMP . '", "'
                . self::DUMP_TYPE_MONGODUMP_TAR_GZ . '"].'
            );
        }
    }

    public function _before(TestInterface $test)
    {
        if ($this->config['cleanup'] && !$this->populated) {
            $this->cleanup();
            $this->loadDump();
        }
    }

    public function _after(TestInterface $test)
    {
        $this->populated = false;
    }

    protected function cleanup()
    {
        $dbh = $this->driver->getDbh();
        if (!$dbh) {
            throw new ModuleConfigException(
                __CLASS__,
                "No connection to database. Remove this module from config if you don't need database repopulation"
            );
        }
        try {
            $this->driver->cleanup();
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
    }

    protected function loadDump()
    {
        $this->validateDump();

        if ($this->isDumpFileEmpty) {
            return;
        }

        try {
            if ($this->config['dump_type'] === self::DUMP_TYPE_JS) {
                $this->driver->load($this->dumpFile);
            }
            if ($this->config['dump_type'] === self::DUMP_TYPE_MONGODUMP) {
                $this->driver->setQuiet($this->config['quiet']);
                $this->driver->loadFromMongoDump($this->dumpFile);
            }
            if ($this->config['dump_type'] === self::DUMP_TYPE_MONGODUMP_TAR_GZ) {
                $this->driver->setQuiet($this->config['quiet']);
                $this->driver->loadFromTarGzMongoDump($this->dumpFile);
            }
        } catch (\Exception $e) {
            throw new ModuleException(__CLASS__, $e->getMessage());
        }
    }

    
    public function useDatabase($dbName)
    {
        $this->driver->setDatabase($dbName);
    }

    
    public function haveInCollection($collection, array $data)
    {
        $collection = $this->driver->getDbh()->selectCollection($collection);
        if ($this->driver->isLegacy()) {
            $collection->insert($data);
            return $data['_id'];
        } else {
            $response = $collection->insertOne($data);
            return $response->getInsertedId()->__toString();
        }
    }

    
    public function seeInCollection($collection, $criteria = [])
    {
        $collection = $this->driver->getDbh()->selectCollection($collection);
        $res = $collection->count($criteria);
        \PHPUnit_Framework_Assert::assertGreaterThan(0, $res);
    }

    
    public function dontSeeInCollection($collection, $criteria = [])
    {
        $collection = $this->driver->getDbh()->selectCollection($collection);
        $res = $collection->count($criteria);
        \PHPUnit_Framework_Assert::assertLessThan(1, $res);
    }

    
    public function grabFromCollection($collection, $criteria = [])
    {
        $collection = $this->driver->getDbh()->selectCollection($collection);
        return $collection->findOne($criteria);
    }

    
    public function grabCollectionCount($collection, $criteria = [])
    {
        $collection = $this->driver->getDbh()->selectCollection($collection);
        return $collection->count($criteria);
    }

    
    public function seeElementIsArray($collection, $criteria = [], $elementToCheck = null)
    {
        $collection = $this->driver->getDbh()->selectCollection($collection);

        $res = $collection->count(
            array_merge(
                $criteria,
                [
                    $elementToCheck => ['$exists' => true],
                    '$where' => "Array.isArray(this.{$elementToCheck})"
                ]
            )
        );
        if ($res > 1) {
            throw new \PHPUnit_Framework_ExpectationFailedException(
                'Error: you should test against a single element criteria when asserting that elementIsArray'
            );
        }
        \PHPUnit_Framework_Assert::assertEquals(1, $res, 'Specified element is not a Mongo Object');
    }

    
    public function seeElementIsObject($collection, $criteria = [], $elementToCheck = null)
    {
        $collection = $this->driver->getDbh()->selectCollection($collection);

        $res = $collection->count(
            array_merge(
                $criteria,
                [
                    $elementToCheck => ['$exists' => true],
                    '$where' => "! Array.isArray(this.{$elementToCheck}) && isObject(this.{$elementToCheck})"
                ]
            )
        );
        if ($res > 1) {
            throw new \PHPUnit_Framework_ExpectationFailedException(
                'Error: you should test against a single element criteria when asserting that elementIsObject'
            );
        }
        \PHPUnit_Framework_Assert::assertEquals(1, $res, 'Specified element is not a Mongo Object');
    }

    
    public function seeNumElementsInCollection($collection, $expected, $criteria = [])
    {
        $collection = $this->driver->getDbh()->selectCollection($collection);
        $res = $collection->count($criteria);
        \PHPUnit_Framework_Assert::assertSame($expected, $res);
    }

    
    public function _requires()
    {
        return ['MongoDB\Client' => '"mongodb/mongodb": "^1.0"'];
    }
}
