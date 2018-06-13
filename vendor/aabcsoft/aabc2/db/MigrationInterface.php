<?php


namespace aabc\db;


interface MigrationInterface
{
    
    public function up();

    
    public function down();
}
