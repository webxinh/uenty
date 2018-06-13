<?php


namespace aabc\db\mssql;


class SqlsrvPDO extends \PDO
{
    
    public function lastInsertId($sequence = null)
    {
        return !$sequence ? parent::lastInsertId() : parent::lastInsertId($sequence);
    }
}
