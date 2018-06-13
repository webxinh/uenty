<?php
use Codeception\Module\OrderHelper;

class CodeTest extends \Codeception\Test\Unit
{
    public function testThis()
    {
        OrderHelper::appendToFile('C');
    }

    public static function setUpBeforeClass()
    {
        OrderHelper::appendToFile('{');
    }

    public static function tearDownAfterClass()
    {
        OrderHelper::appendToFile('}');
    }

    
    public function before()
    {
        OrderHelper::appendToFile('<');
    }

    
    public function after()
    {
        OrderHelper::appendToFile('>');
    }

    
    public static function beforeClass()
    {
        OrderHelper::appendToFile('{');
    }

    
    public static function afterClass()
    {
        OrderHelper::appendToFile('}');
    }
}