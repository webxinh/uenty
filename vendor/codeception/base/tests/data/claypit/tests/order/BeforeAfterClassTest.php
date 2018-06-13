<?php

class BeforeAfterClassTest extends \Codeception\Test\Unit
{
    
    public static function setUpSomeSharedFixtures()
    {
        \Codeception\Module\OrderHelper::appendToFile('{');
    }

    public function testOne()
    {
        \Codeception\Module\OrderHelper::appendToFile('1');
    }

    public function testTwo()
    {
        \Codeception\Module\OrderHelper::appendToFile('2');
    }

    
    public static function tearDownSomeSharedFixtures()
    {
        \Codeception\Module\OrderHelper::appendToFile('}');
    }

}