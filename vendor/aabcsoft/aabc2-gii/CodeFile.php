<?php


namespace aabc\gii;

use Aabc;
use aabc\base\Object;
use aabc\gii\components\DiffRendererHtmlInline;
use aabc\helpers\Html;


class CodeFile extends Object
{
    
    const OP_CREATE = 'create';
    
    const OP_OVERWRITE = 'overwrite';
    
    const OP_SKIP = 'skip';

    
    public $id;
    
    public $path;
    
    public $content;
    
    public $operation;


    
    public function __construct($path, $content)
    {
        $this->path = strtr($path, '/\\', DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR);
        $this->content = $content;
        $this->id = md5($this->path);
        if (is_file($path)) {
            $this->operation = file_get_contents($path) === $content ? self::OP_SKIP : self::OP_OVERWRITE;
        } else {
            $this->operation = self::OP_CREATE;
        }
    }

    
    public function save()
    {
        $module = Aabc::$app->controller->module;
        if ($this->operation === self::OP_CREATE) {
            $dir = dirname($this->path);
            if (!is_dir($dir)) {
                $mask = @umask(0);
                $result = @mkdir($dir, $module->newDirMode, true);
                @umask($mask);
                if (!$result) {
                    return "Unable to create the directory '$dir'.";
                }
            }
        }
        if (@file_put_contents($this->path, $this->content) === false) {
            return "Unable to write the file '{$this->path}'.";
        } else {
            $mask = @umask(0);
            @chmod($this->path, $module->newFileMode);
            @umask($mask);
        }

        return true;
    }

    
    public function getRelativePath()
    {
        if (strpos($this->path, Aabc::$app->basePath) === 0) {
            return substr($this->path, strlen(Aabc::$app->basePath) + 1);
        } else {
            return $this->path;
        }
    }

    
    public function getType()
    {
        if (($pos = strrpos($this->path, '.')) !== false) {
            return substr($this->path, $pos + 1);
        } else {
            return 'unknown';
        }
    }

    
    public function preview()
    {
        if (($pos = strrpos($this->path, '.')) !== false) {
            $type = substr($this->path, $pos + 1);
        } else {
            $type = 'unknown';
        }

        if ($type === 'php') {
            return highlight_string($this->content, true);
        } elseif (!in_array($type, ['jpg', 'gif', 'png', 'exe'])) {
            return nl2br(Html::encode($this->content));
        } else {
            return false;
        }
    }

    
    public function diff()
    {
        $type = strtolower($this->getType());
        if (in_array($type, ['jpg', 'gif', 'png', 'exe'])) {
            return false;
        } elseif ($this->operation === self::OP_OVERWRITE) {
            return $this->renderDiff(file($this->path), $this->content);
        } else {
            return '';
        }
    }

    
    private function renderDiff($lines1, $lines2)
    {
        if (!is_array($lines1)) {
            $lines1 = explode("\n", $lines1);
        }
        if (!is_array($lines2)) {
            $lines2 = explode("\n", $lines2);
        }
        foreach ($lines1 as $i => $line) {
            $lines1[$i] = rtrim($line, "\r\n");
        }
        foreach ($lines2 as $i => $line) {
            $lines2[$i] = rtrim($line, "\r\n");
        }

        $renderer = new DiffRendererHtmlInline();
        $diff = new \Diff($lines1, $lines2);

        return $diff->render($renderer);
    }
}
