<?php


namespace aabc\web;

use Aabc;
use aabc\db\Connection;
use aabc\db\Query;
use aabc\base\InvalidConfigException;
use aabc\di\Instance;


class DbSession extends MultiFieldSession
{
    
    public $db = 'db';
    
    public $sessionTable = '{{%session}}';


    
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
    }

    
    public function regenerateID($deleteOldSession = false)
    {
        $oldID = session_id();

        // if no session is started, there is nothing to regenerate
        if (empty($oldID)) {
            return;
        }

        parent::regenerateID(false);
        $newID = session_id();
        // if session id regeneration failed, no need to create/update it.
        if (empty($newID)) {
            Aabc::warning('Failed to generate new session ID', __METHOD__);
            return;
        }

        $query = new Query();
        $row = $query->from($this->sessionTable)
            ->where(['id' => $oldID])
            ->createCommand($this->db)
            ->queryOne();
        if ($row !== false) {
            if ($deleteOldSession) {
                $this->db->createCommand()
                    ->update($this->sessionTable, ['id' => $newID], ['id' => $oldID])
                    ->execute();
            } else {
                $row['id'] = $newID;
                $this->db->createCommand()
                    ->insert($this->sessionTable, $row)
                    ->execute();
            }
        } else {
            // shouldn't reach here normally
            $this->db->createCommand()
                ->insert($this->sessionTable, $this->composeFields($newID, ''))
                ->execute();
        }
    }

    
    public function readSession($id)
    {
        $query = new Query();
        $query->from($this->sessionTable)
            ->where('[[expire]]>:expire AND [[id]]=:id', [':expire' => time(), ':id' => $id]);

        if ($this->readCallback !== null) {
            $fields = $query->one($this->db);
            return $fields === false ? '' : $this->extractData($fields);
        }

        $data = $query->select(['data'])->scalar($this->db);
        return $data === false ? '' : $data;
    }

    
    public function writeSession($id, $data)
    {
        // exception must be caught in session write handler
        // http://us.php.net/manual/en/function.session-set-save-handler.php#refsect1-function.session-set-save-handler-notes
        try {
            $query = new Query;
            $exists = $query->select(['id'])
                ->from($this->sessionTable)
                ->where(['id' => $id])
                ->createCommand($this->db)
                ->queryScalar();
            $fields = $this->composeFields($id, $data);
            if ($exists === false) {
                $this->db->createCommand()
                    ->insert($this->sessionTable, $fields)
                    ->execute();
            } else {
                unset($fields['id']);
                $this->db->createCommand()
                    ->update($this->sessionTable, $fields, ['id' => $id])
                    ->execute();
            }
        } catch (\Exception $e) {
            $exception = ErrorHandler::convertExceptionToString($e);
            // its too late to use Aabc logging here
            error_log($exception);
            if (AABC_DEBUG) {
                echo $exception;
            }
            return false;
        }

        return true;
    }

    
    public function destroySession($id)
    {
        $this->db->createCommand()
            ->delete($this->sessionTable, ['id' => $id])
            ->execute();

        return true;
    }

    
    public function gcSession($maxLifetime)
    {
        $this->db->createCommand()
            ->delete($this->sessionTable, '[[expire]]<:expire', [':expire' => time()])
            ->execute();

        return true;
    }
}
