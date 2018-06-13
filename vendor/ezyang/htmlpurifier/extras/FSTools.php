<?php


class FSTools
{

    private static $singleton;

    
    public static function singleton()
    {
        if (empty(FSTools::$singleton)) FSTools::$singleton = new FSTools();
        return FSTools::$singleton;
    }

    
    public static function setSingleton($singleton)
    {
        FSTools::$singleton = $singleton;
    }

    
    public function mkdirr($folder)
    {
        $folders = preg_split("#[\\\\/]#", $folder);
        $base = '';
        for($i = 0, $c = count($folders); $i < $c; $i++) {
            if(empty($folders[$i])) {
                if (!$i) {
                    // special case for root level
                    $base .= DIRECTORY_SEPARATOR;
                }
                continue;
            }
            $base .= $folders[$i];
            if(!is_dir($base)){
                $this->mkdir($base);
            }
            $base .= DIRECTORY_SEPARATOR;
        }
    }

    
    public function copyr($source, $dest)
    {
        // Simple copy for a file
        if (is_file($source)) {
            return $this->copy($source, $dest);
        }
        // Make destination directory
        if (!is_dir($dest)) {
            $this->mkdir($dest);
        }
        // Loop through the folder
        $dir = $this->dir($source);
        while ( false !== ($entry = $dir->read()) ) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            if (!$this->copyable($entry)) {
                continue;
            }
            // Deep copy directories
            if ($dest !== "$source/$entry") {
                $this->copyr("$source/$entry", "$dest/$entry");
            }
        }
        // Clean up
        $dir->close();
        return true;
    }

    
    public function copyable($file)
    {
        return true;
    }

    
    public function rmdirr($dirname)
    {
        // Sanity check
        if (!$this->file_exists($dirname)) {
            return false;
        }

        // Simple delete for a file
        if ($this->is_file($dirname) || $this->is_link($dirname)) {
            return $this->unlink($dirname);
        }

        // Loop through the folder
        $dir = $this->dir($dirname);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            // Recurse
            $this->rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
        }

        // Clean up
        $dir->close();
        return $this->rmdir($dirname);
    }

    
    public function globr($dir, $pattern, $flags = NULL)
    {
        $files = $this->glob("$dir/$pattern", $flags);
        if ($files === false) $files = array();
        $sub_dirs = $this->glob("$dir/*", GLOB_ONLYDIR);
        if ($sub_dirs === false) $sub_dirs = array();
        foreach ($sub_dirs as $sub_dir) {
            $sub_files = $this->globr($sub_dir, $pattern, $flags);
            $files = array_merge($files, $sub_files);
        }
        return $files;
    }

    
    public function __call($name, $args)
    {
        return call_user_func_array($name, $args);
    }

}

// vim: et sw=4 sts=4
