<?php


namespace aabc\mutex;

use Aabc;
use aabc\base\InvalidConfigException;
use aabc\helpers\FileHelper;


class FileMutex extends Mutex
{
    
    public $mutexPath = '@runtime/mutex';
    
    public $fileMode;
    
    public $dirMode = 0775;

    
    private $_files = [];


    
    public function init()
    {
        $this->mutexPath = Aabc::getAlias($this->mutexPath);
        if (!is_dir($this->mutexPath)) {
            FileHelper::createDirectory($this->mutexPath, $this->dirMode, true);
        }
    }

    
    protected function acquireLock($name, $timeout = 0)
    {
        $file = fopen($this->getLockFilePath($name), 'w+');
        if ($file === false) {
            return false;
        }
        if ($this->fileMode !== null) {
            @chmod($this->getLockFilePath($name), $this->fileMode);
        }
        $waitTime = 0;
        while (!flock($file, LOCK_EX | LOCK_NB)) {
            $waitTime++;
            if ($waitTime > $timeout) {
                fclose($file);

                return false;
            }
            sleep(1);
        }
        $this->_files[$name] = $file;

        return true;
    }

    
    protected function releaseLock($name)
    {
        if (!isset($this->_files[$name]) || !flock($this->_files[$name], LOCK_UN)) {
            return false;
        } else {
            fclose($this->_files[$name]);
            unlink($this->getLockFilePath($name));
            unset($this->_files[$name]);

            return true;
        }
    }

    
    protected function getLockFilePath($name)
    {
        return $this->mutexPath . DIRECTORY_SEPARATOR . md5($name) . '.lock';
    }
}
