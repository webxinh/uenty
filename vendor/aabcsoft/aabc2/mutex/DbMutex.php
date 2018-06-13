<?php


namespace aabc\mutex;

use Aabc;
use aabc\db\Connection;
use aabc\base\InvalidConfigException;
use aabc\di\Instance;


abstract class DbMutex extends Mutex
{
    
    public $db = 'db';


    
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
    }
}
