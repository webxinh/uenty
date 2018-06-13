<?php
namespace Codeception\Lib\Interfaces;

interface Db
{
    
    public function seeInDatabase($table, $criteria = []);

    
    public function dontSeeInDatabase($table, $criteria = []);

    
    public function grabFromDatabase($table, $column, $criteria = []);
}
