<?php


namespace aabc\log;

use Aabc;
use aabc\db\Connection;
use aabc\base\InvalidConfigException;
use aabc\di\Instance;
use aabc\helpers\VarDumper;


class DbTarget extends Target
{
    
    public $db = 'db';
    
    public $logTable = '{{%log}}';


    
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
    }

    
    public function export()
    {
        $tableName = $this->db->quoteTableName($this->logTable);
        $sql = "INSERT INTO $tableName ([[level]], [[category]], [[log_time]], [[prefix]], [[message]])
                VALUES (:level, :category, :log_time, :prefix, :message)";
        $command = $this->db->createCommand($sql);
        foreach ($this->messages as $message) {
            list($text, $level, $category, $timestamp) = $message;
            if (!is_string($text)) {
                // exceptions may not be serializable if in the call stack somewhere is a Closure
                if ($text instanceof \Throwable || $text instanceof \Exception) {
                    $text = (string) $text;
                } else {
                    $text = VarDumper::export($text);
                }
            }
            $command->bindValues([
                ':level' => $level,
                ':category' => $category,
                ':log_time' => $timestamp,
                ':prefix' => $this->getMessagePrefix($message),
                ':message' => $text,
            ])->execute();
        }
    }
}
