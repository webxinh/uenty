<?php


namespace aabc\test;

use Aabc;


class InitDbFixture extends DbFixture
{
    
    public $initScript = '@app/tests/fixtures/initdb.php';
    
    public $schemas = [''];


    
    public function beforeLoad()
    {
        $this->checkIntegrity(false);
    }

    
    public function afterLoad()
    {
        $this->checkIntegrity(true);
    }

    
    public function load()
    {
        $file = Aabc::getAlias($this->initScript);
        if (is_file($file)) {
            require($file);
        }
    }

    
    public function beforeUnload()
    {
        $this->checkIntegrity(false);
    }

    
    public function afterUnload()
    {
        $this->checkIntegrity(true);
    }

    
    public function checkIntegrity($check)
    {
        foreach ($this->schemas as $schema) {
            $this->db->createCommand()->checkIntegrity($check, $schema)->execute();
        }
    }
}
