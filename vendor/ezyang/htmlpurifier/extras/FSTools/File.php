<?php


class FSTools_File
{

    
    protected $name;

    
    protected $handle = false;

    
    protected $fs;

    
    public function __construct($name, $fs = false)
    {
        $this->name = $name;
        $this->fs = $fs ? $fs : FSTools::singleton();
    }

    
    public function getName() {return $this->name;}

    
    public function getDirectory() {return $this->fs->dirname($this->name);}

    
    public function get()
    {
        return $this->fs->file_get_contents($this->name);
    }

    
    public function write($contents)
    {
        return $this->fs->file_put_contents($this->name, $contents);
    }

    
    public function delete()
    {
        return $this->fs->unlink($this->name);
    }

    
    public function exists()
    {
        return $this->fs->is_file($this->name);
    }

    
    public function getMTime()
    {
        return $this->fs->filemtime($this->name);
    }

    
    public function chmod($octal_code)
    {
        return @$this->fs->chmod($this->name, $octal_code);
    }

    
    public function open($mode)
    {
        if ($this->handle) $this->close();
        $this->handle = $this->fs->fopen($this->name, $mode);
        return true;
    }

    
    public function close()
    {
        if (!$this->handle) return false;
        $status = $this->fs->fclose($this->handle);
        $this->handle = false;
        return $status;
    }

    
    public function getLine($length = null)
    {
        if (!$this->handle) $this->open('r');
        if ($length === null) return $this->fs->fgets($this->handle);
        else return $this->fs->fgets($this->handle, $length);
    }

    
    public function getChar()
    {
        if (!$this->handle) $this->open('r');
        return $this->fs->fgetc($this->handle);
    }

    
    public function read($length)
    {
        if (!$this->handle) $this->open('r');
        return $this->fs->fread($this->handle, $length);
    }

    
    public function put($string)
    {
        if (!$this->handle) $this->open('a');
        return $this->fs->fwrite($this->handle, $string);
    }

    
    public function eof()
    {
        if (!$this->handle) return true;
        return $this->fs->feof($this->handle);
    }

    public function __destruct()
    {
        if ($this->handle) $this->close();
    }

}

// vim: et sw=4 sts=4
