<?php
namespace Codeception\Module;

use Codeception\Lib\Interfaces\DataMapper;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\Interfaces\ORM;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Interfaces\RequiresPackage;
use Codeception\TestInterface;
use League\FactoryMuffin\FactoryMuffin;
use League\FactoryMuffin\Stores\RepositoryStore;


class DataFactory extends \Codeception\Module implements DependsOnModule, RequiresPackage
{
    protected $dependencyMessage = <<<EOF
ORM module (like Doctrine2) or Framework module with ActiveRecord support is required:
--
modules:
    enabled:
        - DataFactory:
            depends: Doctrine2
--
EOF;

    
    public $ormModule;

    
    public $factoryMuffin;

    protected $config = ['factories' => null];

    public function _requires()
    {
        return [
            'League\FactoryMuffin\FactoryMuffin' => '"league/factory-muffin": "^3.0"',
            'League\FactoryMuffin\Faker\Facade' => '"league/factory-muffin-faker": "^1.0"'
        ];
    }

    public function _beforeSuite($settings = [])
    {
        $store = null;
        if ($this->ormModule instanceof DataMapper) { // for Doctrine
            $store = new RepositoryStore($this->ormModule->_getEntityManager());
        }
        $this->factoryMuffin = new FactoryMuffin($store);

        if ($this->config['factories']) {
            foreach ((array) $this->config['factories'] as $factoryPath) {
                $realpath = realpath(codecept_root_dir().$factoryPath);
                if ($realpath === false) {
                    throw new ModuleException($this, 'The path to one of your factories is not correct. Please specify the directory relative to the codeception.yml file (ie. _support/factories).');
                }
                $this->factoryMuffin->loadFactories($realpath);
            }
        }
    }

    public function _inject(ORM $orm)
    {
        $this->ormModule = $orm;
    }

    public function _after(TestInterface $test)
    {
        if ($this->ormModule->_getConfig('cleanup')) {
            return; // don't delete records if ORM is set with cleanup
        }
        $this->factoryMuffin->deleteSaved();
    }

    public function _depends()
    {
        return ['Codeception\Lib\Interfaces\ORM' => $this->dependencyMessage];
    }

    
    public function _define($model, $fields)
    {
        return $this->factoryMuffin->define($model)->setDefinitions($fields);
    }

    
    public function have($name, array $extraAttrs = [])
    {
        return $this->factoryMuffin->create($name, $extraAttrs);
    }

    
    public function haveMultiple($name, $times, array $extraAttrs = [])
    {
        return $this->factoryMuffin->seed($times, $name, $extraAttrs);
    }
}
