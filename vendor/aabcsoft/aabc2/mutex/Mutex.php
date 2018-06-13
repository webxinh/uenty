<?php


namespace aabc\mutex;

use Aabc;
use aabc\base\Component;


abstract class Mutex extends Component
{
    
    public $autoRelease = true;

    
    private $_locks = [];


    
    public function init()
    {
        if ($this->autoRelease) {
            $locks = &$this->_locks;
            register_shutdown_function(function () use (&$locks) {
                foreach ($locks as $lock) {
                    $this->release($lock);
                }
            });
        }
    }

    
    public function acquire($name, $timeout = 0)
    {
        if ($this->acquireLock($name, $timeout)) {
            $this->_locks[] = $name;

            return true;
        } else {
            return false;
        }
    }

    
    public function release($name)
    {
        if ($this->releaseLock($name)) {
            $index = array_search($name, $this->_locks);
            if ($index !== false) {
                unset($this->_locks[$index]);
            }

            return true;
        } else {
            return false;
        }
    }

    
    abstract protected function acquireLock($name, $timeout = 0);

    
    abstract protected function releaseLock($name);
}
