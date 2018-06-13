<?php


namespace aabc\db;

use Aabc;
use aabc\base\InvalidConfigException;


class Transaction extends \aabc\base\Object
{
    
    const READ_UNCOMMITTED = 'READ UNCOMMITTED';
    
    const READ_COMMITTED = 'READ COMMITTED';
    
    const REPEATABLE_READ = 'REPEATABLE READ';
    
    const SERIALIZABLE = 'SERIALIZABLE';

    
    public $db;

    
    private $_level = 0;


    
    public function getIsActive()
    {
        return $this->_level > 0 && $this->db && $this->db->isActive;
    }

    
    public function begin($isolationLevel = null)
    {
        if ($this->db === null) {
            throw new InvalidConfigException('Transaction::db must be set.');
        }
        $this->db->open();

        if ($this->_level === 0) {
            if ($isolationLevel !== null) {
                $this->db->getSchema()->setTransactionIsolationLevel($isolationLevel);
            }
            Aabc::trace('Begin transaction' . ($isolationLevel ? ' with isolation level ' . $isolationLevel : ''), __METHOD__);

            $this->db->trigger(Connection::EVENT_BEGIN_TRANSACTION);
            $this->db->pdo->beginTransaction();
            $this->_level = 1;

            return;
        }

        $schema = $this->db->getSchema();
        if ($schema->supportsSavepoint()) {
            Aabc::trace('Set savepoint ' . $this->_level, __METHOD__);
            $schema->createSavepoint('LEVEL' . $this->_level);
        } else {
            Aabc::info('Transaction not started: nested transaction not supported', __METHOD__);
        }
        $this->_level++;
    }

    
    public function commit()
    {
        if (!$this->getIsActive()) {
            throw new Exception('Failed to commit transaction: transaction was inactive.');
        }

        $this->_level--;
        if ($this->_level === 0) {
            Aabc::trace('Commit transaction', __METHOD__);
            $this->db->pdo->commit();
            $this->db->trigger(Connection::EVENT_COMMIT_TRANSACTION);
            return;
        }

        $schema = $this->db->getSchema();
        if ($schema->supportsSavepoint()) {
            Aabc::trace('Release savepoint ' . $this->_level, __METHOD__);
            $schema->releaseSavepoint('LEVEL' . $this->_level);
        } else {
            Aabc::info('Transaction not committed: nested transaction not supported', __METHOD__);
        }
    }

    
    public function rollBack()
    {
        if (!$this->getIsActive()) {
            // do nothing if transaction is not active: this could be the transaction is committed
            // but the event handler to "commitTransaction" throw an exception
            return;
        }

        $this->_level--;
        if ($this->_level === 0) {
            Aabc::trace('Roll back transaction', __METHOD__);
            $this->db->pdo->rollBack();
            $this->db->trigger(Connection::EVENT_ROLLBACK_TRANSACTION);
            return;
        }

        $schema = $this->db->getSchema();
        if ($schema->supportsSavepoint()) {
            Aabc::trace('Roll back to savepoint ' . $this->_level, __METHOD__);
            $schema->rollBackSavepoint('LEVEL' . $this->_level);
        } else {
            Aabc::info('Transaction not rolled back: nested transaction not supported', __METHOD__);
            // throw an exception to fail the outer transaction
            throw new Exception('Roll back failed: nested transaction not supported.');
        }
    }

    
    public function setIsolationLevel($level)
    {
        if (!$this->getIsActive()) {
            throw new Exception('Failed to set isolation level: transaction was inactive.');
        }
        Aabc::trace('Setting transaction isolation level to ' . $level, __METHOD__);
        $this->db->getSchema()->setTransactionIsolationLevel($level);
    }

    
    public function getLevel()
    {
        return $this->_level;
    }
}
