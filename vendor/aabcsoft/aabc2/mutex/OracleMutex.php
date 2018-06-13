<?php


namespace aabc\mutex;

use PDO;
use Aabc;
use aabc\base\InvalidConfigException;


class OracleMutex extends DbMutex
{
    
    const MODE_X = 'X_MODE';
    const MODE_NL = 'NL_MODE';
    const MODE_S = 'S_MODE';
    const MODE_SX = 'SX_MODE';
    const MODE_SS = 'SS_MODE';
    const MODE_SSX = 'SSX_MODE';

    
    public $lockMode = self::MODE_X;
    
    public $releaseOnCommit = false;


    
    public function init()
    {
        parent::init();
        if (strpos($this->db->driverName, 'oci') !== 0 && strpos($this->db->driverName, 'odbc') !== 0) {
            throw new InvalidConfigException('In order to use OracleMutex connection must be configured to use Oracle database.');
        }
    }

    
    protected function acquireLock($name, $timeout = 0)
    {
        $lockStatus = null;

        
        $releaseOnCommit = $this->releaseOnCommit ? 'TRUE' : 'FALSE';
        $timeout = abs((int)$timeout);

        
        $this->db->createCommand(
                'DECLARE
    handle VARCHAR2(128);
BEGIN
    DBMS_LOCK.ALLOCATE_UNIQUE(:name, handle);
    :lockStatus := DBMS_LOCK.REQUEST(handle, DBMS_LOCK.' . $this->lockMode . ', ' . $timeout . ', ' . $releaseOnCommit . ');
END;',
                [':name' => $name]
            )
            ->bindParam(':lockStatus', $lockStatus, PDO::PARAM_INT, 1)
            ->execute();

        return ($lockStatus === 0 || $lockStatus === '0');
    }

    
    protected function releaseLock($name)
    {
        $releaseStatus = null;
        $this->db->createCommand(
                'DECLARE
    handle VARCHAR2(128);
BEGIN
    DBMS_LOCK.ALLOCATE_UNIQUE(:name, handle);
    :result := DBMS_LOCK.RELEASE(handle);
END;',
                [':name' => $name]
            )
            ->bindParam(':result', $releaseStatus, PDO::PARAM_INT, 1)
            ->execute();

        return ($releaseStatus === 0 || $releaseStatus === '0');
    }
}
