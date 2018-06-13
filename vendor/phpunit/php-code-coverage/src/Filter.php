<?php
/*
 * This file is part of the php-code-coverage package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\CodeCoverage;


class Filter
{
    
    private $whitelistedFiles = [];

    
    public function addDirectoryToWhitelist($directory, $suffix = '.php', $prefix = '')
    {
        $facade = new \File_Iterator_Facade;
        $files  = $facade->getFilesAsArray($directory, $suffix, $prefix);

        foreach ($files as $file) {
            $this->addFileToWhitelist($file);
        }
    }

    
    public function addFileToWhitelist($filename)
    {
        $this->whitelistedFiles[realpath($filename)] = true;
    }

    
    public function addFilesToWhitelist(array $files)
    {
        foreach ($files as $file) {
            $this->addFileToWhitelist($file);
        }
    }

    
    public function removeDirectoryFromWhitelist($directory, $suffix = '.php', $prefix = '')
    {
        $facade = new \File_Iterator_Facade;
        $files  = $facade->getFilesAsArray($directory, $suffix, $prefix);

        foreach ($files as $file) {
            $this->removeFileFromWhitelist($file);
        }
    }

    
    public function removeFileFromWhitelist($filename)
    {
        $filename = realpath($filename);

        unset($this->whitelistedFiles[$filename]);
    }

    
    public function isFile($filename)
    {
        if ($filename == '-' ||
            strpos($filename, 'vfs://') === 0 ||
            strpos($filename, 'xdebug://debug-eval') !== false ||
            strpos($filename, 'eval()\'d code') !== false ||
            strpos($filename, 'runtime-created function') !== false ||
            strpos($filename, 'runkit created function') !== false ||
            strpos($filename, 'assert code') !== false ||
            strpos($filename, 'regexp code') !== false) {
            return false;
        }

        return file_exists($filename);
    }

    
    public function isFiltered($filename)
    {
        if (!$this->isFile($filename)) {
            return true;
        }

        $filename = realpath($filename);

        return !isset($this->whitelistedFiles[$filename]);
    }

    
    public function getWhitelist()
    {
        return array_keys($this->whitelistedFiles);
    }

    
    public function hasWhitelist()
    {
        return !empty($this->whitelistedFiles);
    }

    
    public function getWhitelistedFiles()
    {
        return $this->whitelistedFiles;
    }

    
    public function setWhitelistedFiles($whitelistedFiles)
    {
        $this->whitelistedFiles = $whitelistedFiles;
    }
}
