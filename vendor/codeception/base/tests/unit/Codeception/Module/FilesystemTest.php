<?php

use Codeception\Module\Filesystem;
use Codeception\Util\Stub;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{

    
    protected $module;

    public function setUp()
    {
        $this->module = new Filesystem(make_container());
        $this->module->_before(Stub::makeEmpty('\Codeception\Test\Test'));
    }


    public function tearDown()
    {
        $this->module->_after(Stub::makeEmpty('\Codeception\Test\Test'));
    }

    public function testSeeFileFoundPassesWhenFileExists()
    {
        $this->module->seeFileFound('tests/data/dumps/mysql.sql');
    }

    public function testSeeFileFoundPassesWhenFileExistsInSubdirectoryOfPath()
    {
        $this->module->seeFileFound('mysql.sql', 'tests/data/');
    }

    
    public function testSeeFileFoundFailsWhenFileDoesNotExist()
    {
        $this->module->seeFileFound('does-not-exist');
    }

    
    public function testSeeFileFoundFailsWhenPathDoesNotExist()
    {
        $this->module->seeFileFound('mysql.sql', 'does-not-exist');
    }

    public function testDontSeeFileFoundPassesWhenFileDoesNotExists()
    {
        $this->module->dontSeeFileFound('does-not-exist');
    }

    public function testDontSeeFileFoundPassesWhenFileDoesNotExistsInPath()
    {
        $this->module->dontSeeFileFound('does-not-exist', 'tests/data/');
    }

    
    public function testDontSeeFileFoundFailsWhenFileExists()
    {
        $this->module->dontSeeFileFound('tests/data/dumps/mysql.sql');
    }

    
    public function testDontSeeFileFoundFailsWhenPathDoesNotExist()
    {
        $this->module->dontSeeFileFound('mysql.sql', 'does-not-exist');
    }

    
    public function testDontSeeFileFoundFailsWhenFileExistsInSubdirectoryOfPath()
    {
        $this->module->dontSeeFileFound('mysql.sql', 'tests/data/');
    }
}
