<?php


namespace aabc\test;

use Aabc;
use aabc\base\InvalidConfigException;


trait FixtureTrait
{
    
    private $_fixtures;


    
    public function fixtures()
    {
        return [];
    }

    
    public function globalFixtures()
    {
        return [];
    }

    
    public function loadFixtures($fixtures = null)
    {
        if ($fixtures === null) {
            $fixtures = $this->getFixtures();
        }

        /* @var $fixture Fixture */
        foreach ($fixtures as $fixture) {
            $fixture->beforeLoad();
        }
        foreach ($fixtures as $fixture) {
            $fixture->load();
        }
        foreach (array_reverse($fixtures) as $fixture) {
            $fixture->afterLoad();
        }
    }

    
    public function unloadFixtures($fixtures = null)
    {
        if ($fixtures === null) {
            $fixtures = $this->getFixtures();
        }

        /* @var $fixture Fixture */
        foreach ($fixtures as $fixture) {
            $fixture->beforeUnload();
        }
        $fixtures = array_reverse($fixtures);
        foreach ($fixtures as $fixture) {
            $fixture->unload();
        }
        foreach ($fixtures as $fixture) {
            $fixture->afterUnload();
        }
    }

    
    public function getFixtures()
    {
        if ($this->_fixtures === null) {
            $this->_fixtures = $this->createFixtures(array_merge($this->globalFixtures(), $this->fixtures()));
        }

        return $this->_fixtures;
    }

    
    public function getFixture($name)
    {
        if ($this->_fixtures === null) {
            $this->_fixtures = $this->createFixtures(array_merge($this->globalFixtures(), $this->fixtures()));
        }
        $name = ltrim($name, '\\');

        return isset($this->_fixtures[$name]) ? $this->_fixtures[$name] : null;
    }

    
    protected function createFixtures(array $fixtures)
    {
        // normalize fixture configurations
        $config = [];  // configuration provided in test case
        $aliases = [];  // class name => alias or class name
        foreach ($fixtures as $name => $fixture) {
            if (!is_array($fixture)) {
                $class = ltrim($fixture, '\\');
                $fixtures[$name] = ['class' => $class];
                $aliases[$class] = is_int($name) ? $class : $name;
            } elseif (isset($fixture['class'])) {
                $class = ltrim($fixture['class'], '\\');
                $config[$class] = $fixture;
                $aliases[$class] = $name;
            } else {
                throw new InvalidConfigException("You must specify 'class' for the fixture '$name'.");
            }
        }

        // create fixture instances
        $instances = [];
        $stack = array_reverse($fixtures);
        while (($fixture = array_pop($stack)) !== null) {
            if ($fixture instanceof Fixture) {
                $class = get_class($fixture);
                $name = isset($aliases[$class]) ? $aliases[$class] : $class;
                unset($instances[$name]);  // unset so that the fixture is added to the last in the next line
                $instances[$name] = $fixture;
            } else {
                $class = ltrim($fixture['class'], '\\');
                $name = isset($aliases[$class]) ? $aliases[$class] : $class;
                if (!isset($instances[$name])) {
                    $instances[$name] = false;
                    $stack[] = $fixture = Aabc::createObject($fixture);
                    foreach ($fixture->depends as $dep) {
                        // need to use the configuration provided in test case
                        $stack[] = isset($config[$dep]) ? $config[$dep] : ['class' => $dep];
                    }
                } elseif ($instances[$name] === false) {
                    throw new InvalidConfigException("A circular dependency is detected for fixture '$class'.");
                }
            }
        }

        return $instances;
    }
}
