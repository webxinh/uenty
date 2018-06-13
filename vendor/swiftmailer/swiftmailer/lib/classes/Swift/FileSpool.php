<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier <fabien.potencier@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class Swift_FileSpool extends Swift_ConfigurableSpool
{
    
    private $_path;

    
    private $_retryLimit = 10;

    
    public function __construct($path)
    {
        $this->_path = $path;

        if (!file_exists($this->_path)) {
            if (!mkdir($this->_path, 0777, true)) {
                throw new Swift_IoException(sprintf('Unable to create path "%s".', $this->_path));
            }
        }
    }

    
    public function isStarted()
    {
        return true;
    }

    
    public function start()
    {
    }

    
    public function stop()
    {
    }

    
    public function setRetryLimit($limit)
    {
        $this->_retryLimit = $limit;
    }

    
    public function queueMessage(Swift_Mime_Message $message)
    {
        $ser = serialize($message);
        $fileName = $this->_path.'/'.$this->getRandomString(10);
        for ($i = 0; $i < $this->_retryLimit; ++$i) {
            /* We try an exclusive creation of the file. This is an atomic operation, it avoid locking mechanism */
            $fp = @fopen($fileName.'.message', 'x');
            if (false !== $fp) {
                if (false === fwrite($fp, $ser)) {
                    return false;
                }

                return fclose($fp);
            } else {
                /* The file already exists, we try a longer fileName */
                $fileName .= $this->getRandomString(1);
            }
        }

        throw new Swift_IoException(sprintf('Unable to create a file for enqueuing Message in "%s".', $this->_path));
    }

    
    public function recover($timeout = 900)
    {
        foreach (new DirectoryIterator($this->_path) as $file) {
            $file = $file->getRealPath();

            if (substr($file, -16) == '.message.sending') {
                $lockedtime = filectime($file);
                if ((time() - $lockedtime) > $timeout) {
                    rename($file, substr($file, 0, -8));
                }
            }
        }
    }

    
    public function flushQueue(Swift_Transport $transport, &$failedRecipients = null)
    {
        $directoryIterator = new DirectoryIterator($this->_path);

        /* Start the transport only if there are queued files to send */
        if (!$transport->isStarted()) {
            foreach ($directoryIterator as $file) {
                if (substr($file->getRealPath(), -8) == '.message') {
                    $transport->start();
                    break;
                }
            }
        }

        $failedRecipients = (array) $failedRecipients;
        $count = 0;
        $time = time();
        foreach ($directoryIterator as $file) {
            $file = $file->getRealPath();

            if (substr($file, -8) != '.message') {
                continue;
            }

            /* We try a rename, it's an atomic operation, and avoid locking the file */
            if (rename($file, $file.'.sending')) {
                $message = unserialize(file_get_contents($file.'.sending'));

                $count += $transport->send($message, $failedRecipients);

                unlink($file.'.sending');
            } else {
                /* This message has just been catched by another process */
                continue;
            }

            if ($this->getMessageLimit() && $count >= $this->getMessageLimit()) {
                break;
            }

            if ($this->getTimeLimit() && (time() - $time) >= $this->getTimeLimit()) {
                break;
            }
        }

        return $count;
    }

    
    protected function getRandomString($count)
    {
        // This string MUST stay FS safe, avoid special chars
        $base = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';
        $ret = '';
        $strlen = strlen($base);
        for ($i = 0; $i < $count; ++$i) {
            $ret .= $base[((int) rand(0, $strlen - 1))];
        }

        return $ret;
    }
}
