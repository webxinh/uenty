<?php
namespace Codeception\Module;

use Codeception\Module\Filesystem;
use Codeception\TestInterface;



class FTP extends Filesystem
{
    
    protected $ftp = null;

    
    protected $config = [
        'type'     => 'ftp',
        'port'     => 21,
        'timeout'  => 90,
        'user'     => 'anonymous',
        'password' => '',
        'key'      => '',
        'tmp'      => 'tests/_data',
        'passive'  => false,
        'cleanup'  => true
    ];

    
    protected $requiredFields = ['host'];

    // ----------- SETUP METHODS BELOW HERE -------------------------//

    
    public function _before(TestInterface $test)
    {
        // Login using config settings
        $this->loginAs($this->config['user'], $this->config['password']);
    }

    
    public function _after(TestInterface $test)
    {
        $this->_closeConnection();

        // Clean up temp files
        if ($this->config['cleanup']) {
            if (file_exists($this->config['tmp'] . '/ftp_data_file.tmp')) {
                unlink($this->config['tmp'] . '/ftp_data_file.tmp');
            }
        }
    }

    
    public function loginAs($user = 'anonymous', $password = '')
    {
        $this->_openConnection($user, $password); // Create new connection and login.
    }

    
    public function amInPath($path)
    {
        $this->_changeDirectory($this->path = $this->absolutizePath($path) . ($path == '/' ? '' : DIRECTORY_SEPARATOR));
        $this->debug('Moved to ' . $this->path);
    }

    
    protected function absolutizePath($path)
    {
        if (strpos($path, '/') === 0) {
            return $path;
        }
        return $this->path . $path;
    }

    // ----------- SEARCH METHODS BELOW HERE ------------------------//

    
    public function seeFileFound($filename, $path = '')
    {
        $files = $this->grabFileList($path);
        $this->debug("see file: {$filename}");
        $this->assertContains($filename, $files, "file {$filename} not found in {$path}");
    }

    
    public function seeFileFoundMatches($regex, $path = '')
    {
        foreach ($this->grabFileList($path) as $filename) {
            preg_match($regex, $filename, $matches);
            if (!empty($matches)) {
                $this->debug("file '{$filename}' matches '{$regex}'");
                return;
            }
        }
        $this->fail("no file matches found for '{$regex}'");
    }

    
    public function dontSeeFileFound($filename, $path = '')
    {
        $files = $this->grabFileList($path);
        $this->debug("don't see file: {$filename}");
        $this->assertNotContains($filename, $files);
    }

    
    public function dontSeeFileFoundMatches($regex, $path = '')
    {
        foreach ($this->grabFileList($path) as $filename) {
            preg_match($regex, $filename, $matches);
            if (!empty($matches)) {
                $this->fail("file matches found for {$regex}");
            }
        }
        $this->assertTrue(true);
        $this->debug("no files match '{$regex}'");
    }

    // ----------- UTILITY METHODS BELOW HERE -------------------------//

    
    public function openFile($filename)
    {
        $this->_openFile($this->absolutizePath($filename));
    }

    
    public function writeToFile($filename, $contents)
    {
        $this->_writeToFile($this->absolutizePath($filename), $contents);
    }

    
    public function makeDir($dirname)
    {
        $this->makeDirectory($this->absolutizePath($dirname));
    }

    
    public function copyDir($src, $dst)
    {
        $this->fail('copyDir() currently unsupported by FTP module');
    }

    
    public function renameFile($filename, $rename)
    {
        $this->renameDirectory($this->absolutizePath($filename), $this->absolutizePath($rename));
    }

    
    public function renameDir($dirname, $rename)
    {
        $this->renameDirectory($this->absolutizePath($dirname), $this->absolutizePath($rename));
    }

    
    public function deleteFile($filename)
    {
        $this->delete($this->absolutizePath($filename));
    }

    
    public function deleteDir($dirname)
    {
        $this->delete($this->absolutizePath($dirname));
    }

    
    public function cleanDir($dirname)
    {
        $this->clearDirectory($this->absolutizePath($dirname));
    }

    // ----------- GRABBER METHODS BELOW HERE -----------------------//


    
    public function grabFileList($path = '', $ignore = true)
    {
        $absolutize_path = $this->absolutizePath($path)
            . ($path != '' && substr($path, -1) != '/' ? DIRECTORY_SEPARATOR : '');
        $files = $this->_listFiles($absolutize_path);

        $display_files = [];
        if (is_array($files) && !empty($files)) {
            $this->debug('File List:');
            foreach ($files as &$file) {
                if (strtolower($file) != '.' &&
                    strtolower($file) != '..' &&
                    strtolower($file) != 'thumbs.db'
                ) { // Ignore '.', '..' and 'thumbs.db'
                    // Replace full path from file listings if returned in listing
                    $file = str_replace(
                        $absolutize_path,
                        '',
                        $file
                    );
                    $display_files[] = $file;
                    $this->debug('    - ' . $file);
                }
            }
            return $ignore ? $display_files : $files;
        }
        $this->debug("File List: <empty>");
        return [];
    }

    
    public function grabFileCount($path = '', $ignore = true)
    {
        $count = count($this->grabFileList($path, $ignore));
        $this->debug("File Count: {$count}");
        return $count;
    }

    
    public function grabFileSize($filename)
    {
        $fileSize = $this->size($filename);
        $this->debug("{$filename} has a file size of {$fileSize}");
        return $fileSize;
    }

    
    public function grabFileModified($filename)
    {
        $time = $this->modified($filename);
        $this->debug("{$filename} was last modified at {$time}");
        return $time;
    }

    
    public function grabDirectory()
    {
        $pwd = $this->_directory();
        $this->debug("PWD: {$pwd}");
        return $pwd;
    }

    // ----------- SERVER CONNECTION METHODS BELOW HERE -------------//

    
    private function _openConnection($user = 'anonymous', $password = '')
    {
        $this->_closeConnection();   // Close connection if already open
        if ($this->isSFTP()) {
            $this->sftpConnect($user, $password);
        } else {
            $this->ftpConnect($user, $password);
        }
        $pwd = $this->grabDirectory();
        $this->path = $pwd . ($pwd == '/' ? '' : DIRECTORY_SEPARATOR);
    }

    
    private function _closeConnection()
    {
        if (!$this->ftp) {
            return;
        }
        if (!$this->isSFTP()) {
            ftp_close($this->ftp);
            $this->ftp = null;
        }
    }

    
    private function _listFiles($path)
    {
        if ($this->isSFTP()) {
            $files = @$this->ftp->nlist($path);
        } else {
            $files = @ftp_nlist($this->ftp, $path);
        }
        if ($files === false) {
            $this->fail("couldn't list files");
        }
        return $files;
    }

    
    private function _directory()
    {
        if ($this->isSFTP()) {
            // == DIRECTORY_SEPARATOR ? '' : $pwd;
            $pwd = @$this->ftp->pwd();
        } else {
            $pwd = @ftp_pwd($this->ftp);
        }
        if (!$pwd) {
            $this->fail("couldn't get current directory");
        }
    }

    
    private function _changeDirectory($path)
    {
        if ($this->isSFTP()) {
            $changed = @$this->ftp->chdir($path);
        } else {
            $changed = @ftp_chdir($this->ftp, $path);
        }
        if (!$changed) {
            $this->fail("couldn't change directory {$path}");
        }
    }

    
    private function _openFile($filename)
    {
        // Check local tmp directory
        if (!is_dir($this->config['tmp']) || !is_writeable($this->config['tmp'])) {
            $this->fail('tmp directory not found or is not writable');
        }

        // Download file to local tmp directory
        $tmp_file = $this->config['tmp'] . "/ftp_data_file.tmp";

        if ($this->isSFTP()) {
            $downloaded = @$this->ftp->get($filename, $tmp_file);
        } else {
            $downloaded = @ftp_get($this->ftp, $tmp_file, $filename, FTP_BINARY);
        }
        if (!$downloaded) {
            $this->fail('failed to download file to tmp directory');
        }

        // Open file content to variable
        if ($this->file = file_get_contents($tmp_file)) {
            $this->filepath = $filename;
        } else {
            $this->fail('failed to open tmp file');
        }
    }

    
    private function _writeToFile($filename, $contents)
    {
        // Check local tmp directory
        if (!is_dir($this->config['tmp']) || !is_writeable($this->config['tmp'])) {
            $this->fail('tmp directory not found or is not writable');
        }

        // Build temp file
        $tmp_file = $this->config['tmp'] . "/ftp_data_file.tmp";
        file_put_contents($tmp_file, $contents);

        // Update variables
        $this->filepath = $tmp_file;
        $this->file = $contents;

        // Upload the file to server
        if ($this->isSFTP()) {
            $uploaded = @$this->ftp->put($filename, $tmp_file, NET_SFTP_LOCAL_FILE);
        } else {
            $uploaded = ftp_put($this->ftp, $filename, $tmp_file, FTP_BINARY);
        }
        if (!$uploaded) {
            $this->fail('failed to upload file to server');
        }
    }

    
    private function makeDirectory($path)
    {
        if ($this->isSFTP()) {
            $created = @$this->ftp->mkdir($path, true);
        } else {
            $created = @ftp_mkdir($this->ftp, $path);
        }
        if (!$created) {
            $this->fail("couldn't make directory {$path}");
        }
        $this->debug("Make directory: {$path}");
    }

    
    private function renameDirectory($path, $rename)
    {
        if ($this->isSFTP()) {
            $renamed = @$this->ftp->rename($path, $rename);
        } else {
            $renamed = @ftp_rename($this->ftp, $path, $rename);
        }
        if (!$renamed) {
            $this->fail("couldn't rename directory {$path} to {$rename}");
        }
        $this->debug("Renamed directory: {$path} to {$rename}");
    }

    
    private function delete($filename, $isDir = false)
    {
        if ($this->isSFTP()) {
            $deleted = @$this->ftp->delete($filename, $isDir);
        } else {
            $deleted = @$this->ftpDelete($filename);
        }
        if (!$deleted) {
            $this->fail("couldn't delete {$filename}");
        }
        $this->debug("Deleted: {$filename}");
    }


    
    private function ftpDelete($directory)
    {
        // here we attempt to delete the file/directory
        if (!(@ftp_rmdir($this->ftp, $directory) || @ftp_delete($this->ftp, $directory))) {
            // if the attempt to delete fails, get the file listing
            $filelist = @ftp_nlist($this->ftp, $directory);

            // loop through the file list and recursively delete the FILE in the list
            foreach ($filelist as $file) {
                $this->ftpDelete($file);
            }

            // if the file list is empty, delete the DIRECTORY we passed
            $this->ftpDelete($directory);
        }
        return true;
    }

    
    private function clearDirectory($path)
    {
        $this->debug("Clear directory: {$path}");
        $this->delete($path);
        $this->makeDirectory($path);
    }

    
    private function size($filename)
    {
        if ($this->isSFTP()) {
            $size = (int)@$this->ftp->size($filename);
        } else {
            $size = @ftp_size($this->ftp, $filename);
        }
        if ($size > 0) {
            return $size;
        }
        $this->fail("couldn't get the file size for {$filename}");
    }

    
    private function modified($filename)
    {
        if ($this->isSFTP()) {
            $info = @$this->ftp->lstat($filename);
            if ($info) {
                return $info['mtime'];
            }
        } else {
            if ($time = @ftp_mdtm($this->ftp, $filename)) {
                return $time;
            }
        }
        $this->fail("couldn't get the file size for {$filename}");
    }

    
    protected function sftpConnect($user, $password)
    {
        $this->ftp = new \Net_SFTP($this->config['host'], $this->config['port'], $this->config['timeout']);
        if ($this->ftp === false) {
            $this->ftp = null;
            $this->fail('failed to connect to ftp server');
        }

        if (isset($this->config['key'])) {
            $keyFile = file_get_contents($this->config['key']);
            $password = new \Crypt_RSA();
            $password->loadKey($keyFile);
        }

        if (!$this->ftp->login($user, $password)) {
            $this->fail('failed to authenticate user');
        }
    }

    
    protected function ftpConnect($user, $password)
    {
        $this->ftp = ftp_connect($this->config['host'], $this->config['port'], $this->config['timeout']);
        if ($this->ftp === false) {
            $this->ftp = null;
            $this->fail('failed to connect to ftp server');
        }

        // Login using given access details
        if (!@ftp_login($this->ftp, $user, $password)) {
            $this->fail('failed to authenticate user');
        }

        // Set passive mode option (ftp only option)
        if (isset($this->config['passive'])) {
            ftp_pasv($this->ftp, $this->config['passive']);
        }
    }

    protected function isSFTP()
    {
        return strtolower($this->config['type']) == 'sftp';
    }
}
