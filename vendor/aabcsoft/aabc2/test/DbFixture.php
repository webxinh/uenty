<?php


namespace aabc\test;

use Aabc;
use aabc\db\Connection;
use aabc\di\Instance;
use aabc\base\Object;


abstract class DbFixture extends Fixture
{
    
    public $db = 'db';


    
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Object::className());
    }
}
