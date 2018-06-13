<?php


namespace aabc\web;

use Aabc;
use aabc\base\Component;
use aabc\base\InvalidConfigException;
use aabc\base\InvalidParamException;
use aabc\helpers\FileHelper;
use aabc\helpers\Url;


class AssetManager extends Component
{
    
    public $bundles = [];
    
    public $basePath = '@webroot/assets';
    
    public $baseUrl = '@web/assets';
    
    public $assetMap = [];
    
    public $linkAssets = false;
    
    public $fileMode;
    
    public $dirMode = 0775;
    
    public $beforeCopy;
    
    public $afterCopy;
    
    public $forceCopy = false;
    
    public $appendTimestamp = false;
    
    public $hashCallback;

    private $_dummyBundles = [];


    
    public function init()
    {
        parent::init();
        $this->basePath = Aabc::getAlias($this->basePath);
        if (!is_dir($this->basePath)) {
            throw new InvalidConfigException("The directory does not exist: {$this->basePath}");
        } elseif (!is_writable($this->basePath)) {
            throw new InvalidConfigException("The directory is not writable by the Web process: {$this->basePath}");
        } else {
            $this->basePath = realpath($this->basePath);
        }
        $this->baseUrl = rtrim(Aabc::getAlias($this->baseUrl), '/');
    }

    
    public function getBundle($name, $publish = true)
    {
        if ($this->bundles === false) {
            return $this->loadDummyBundle($name);
        } elseif (!isset($this->bundles[$name])) {
            return $this->bundles[$name] = $this->loadBundle($name, [], $publish);
        } elseif ($this->bundles[$name] instanceof AssetBundle) {
            return $this->bundles[$name];
        } elseif (is_array($this->bundles[$name])) {
            return $this->bundles[$name] = $this->loadBundle($name, $this->bundles[$name], $publish);
        } elseif ($this->bundles[$name] === false) {
            return $this->loadDummyBundle($name);
        } else {
            throw new InvalidConfigException("Invalid asset bundle configuration: $name");
        }
    }

    
    protected function loadBundle($name, $config = [], $publish = true)
    {
        if (!isset($config['class'])) {
            $config['class'] = $name;
        }
        /* @var $bundle AssetBundle */
        $bundle = Aabc::createObject($config);
        if ($publish) {
            $bundle->publish($this);
        }
        return $bundle;
    }

    
    protected function loadDummyBundle($name)
    {
        if (!isset($this->_dummyBundles[$name])) {
            $this->_dummyBundles[$name] = $this->loadBundle($name, [
                'sourcePath' => null,
                'js' => [],
                'css' => [],
                'depends' => [],
            ]);
        }
        return $this->_dummyBundles[$name];
    }

    
    public function getAssetUrl($bundle, $asset)
    {
        if (($actualAsset = $this->resolveAsset($bundle, $asset)) !== false) {
            if (strncmp($actualAsset, '@web/', 5) === 0) {
                $asset = substr($actualAsset, 5);
                $basePath = Aabc::getAlias('@webroot');
                $baseUrl = Aabc::getAlias('@web');
            } else {
                $asset = Aabc::getAlias($actualAsset);
                $basePath = $this->basePath;
                $baseUrl = $this->baseUrl;
            }
        } else {
            $basePath = $bundle->basePath;
            $baseUrl = $bundle->baseUrl;
        }

        if (!Url::isRelative($asset) || strncmp($asset, '/', 1) === 0) {
            return $asset;
        }

        if ($this->appendTimestamp && ($timestamp = @filemtime("$basePath/$asset")) > 0) {
            return "$baseUrl/$asset?v=$timestamp";
        } else {
            return "$baseUrl/$asset";
        }
    }

    
    public function getAssetPath($bundle, $asset)
    {
        if (($actualAsset = $this->resolveAsset($bundle, $asset)) !== false) {
            return Url::isRelative($actualAsset) ? $this->basePath . '/' . $actualAsset : false;
        } else {
            return Url::isRelative($asset) ? $bundle->basePath . '/' . $asset : false;
        }
    }

    
    protected function resolveAsset($bundle, $asset)
    {
        if (isset($this->assetMap[$asset])) {
            return $this->assetMap[$asset];
        }
        if ($bundle->sourcePath !== null && Url::isRelative($asset)) {
            $asset = $bundle->sourcePath . '/' . $asset;
        }

        $n = mb_strlen($asset, Aabc::$app->charset);
        foreach ($this->assetMap as $from => $to) {
            $n2 = mb_strlen($from, Aabc::$app->charset);
            if ($n2 <= $n && substr_compare($asset, $from, $n - $n2, $n2) === 0) {
                return $to;
            }
        }

        return false;
    }

    private $_converter;

    
    public function getConverter()
    {
        if ($this->_converter === null) {
            $this->_converter = Aabc::createObject(AssetConverter::className());
        } elseif (is_array($this->_converter) || is_string($this->_converter)) {
            if (is_array($this->_converter) && !isset($this->_converter['class'])) {
                $this->_converter['class'] = AssetConverter::className();
            }
            $this->_converter = Aabc::createObject($this->_converter);
        }

        return $this->_converter;
    }

    
    public function setConverter($value)
    {
        $this->_converter = $value;
    }

    
    private $_published = [];

    
    public function publish($path, $options = [])
    {
        $path = Aabc::getAlias($path);

        if (isset($this->_published[$path])) {
            return $this->_published[$path];
        }

        if (!is_string($path) || ($src = realpath($path)) === false) {
            throw new InvalidParamException("The file or directory to be published does not exist: $path");
        }

        if (is_file($src)) {
            return $this->_published[$path] = $this->publishFile($src);
        } else {
            return $this->_published[$path] = $this->publishDirectory($src, $options);
        }
    }

    
    protected function publishFile($src)
    {
        $dir = $this->hash($src);
        $fileName = basename($src);
        $dstDir = $this->basePath . DIRECTORY_SEPARATOR . $dir;
        $dstFile = $dstDir . DIRECTORY_SEPARATOR . $fileName;

        if (!is_dir($dstDir)) {
            FileHelper::createDirectory($dstDir, $this->dirMode, true);
        }

        if ($this->linkAssets) {
            if (!is_file($dstFile)) {
                symlink($src, $dstFile);
            }
        } elseif (@filemtime($dstFile) < @filemtime($src)) {
            copy($src, $dstFile);
            if ($this->fileMode !== null) {
                @chmod($dstFile, $this->fileMode);
            }
        }

        return [$dstFile, $this->baseUrl . "/$dir/$fileName"];
    }

    
    protected function publishDirectory($src, $options)
    {
        $dir = $this->hash($src);
        $dstDir = $this->basePath . DIRECTORY_SEPARATOR . $dir;
        if ($this->linkAssets) {
            if (!is_dir($dstDir)) {
                FileHelper::createDirectory(dirname($dstDir), $this->dirMode, true);
                symlink($src, $dstDir);
            }
        } elseif (!empty($options['forceCopy']) || ($this->forceCopy && !isset($options['forceCopy'])) || !is_dir($dstDir)) {
            $opts = array_merge(
                $options,
                [
                    'dirMode' => $this->dirMode,
                    'fileMode' => $this->fileMode,
                ]
            );
            if (!isset($opts['beforeCopy'])) {
                if ($this->beforeCopy !== null) {
                    $opts['beforeCopy'] = $this->beforeCopy;
                } else {
                    $opts['beforeCopy'] = function ($from, $to) {
                        return strncmp(basename($from), '.', 1) !== 0;
                    };
                }
            }
            if (!isset($opts['afterCopy']) && $this->afterCopy !== null) {
                $opts['afterCopy'] = $this->afterCopy;
            }
            FileHelper::copyDirectory($src, $dstDir, $opts);
        }

        return [$dstDir, $this->baseUrl . '/' . $dir];
    }

    
    public function getPublishedPath($path)
    {
        $path = Aabc::getAlias($path);

        if (isset($this->_published[$path])) {
            return $this->_published[$path][0];
        }
        if (is_string($path) && ($path = realpath($path)) !== false) {
            return $this->basePath . DIRECTORY_SEPARATOR . $this->hash($path) . (is_file($path) ? DIRECTORY_SEPARATOR . basename($path) : '');
        } else {
            return false;
        }
    }

    
    public function getPublishedUrl($path)
    {
        $path = Aabc::getAlias($path);

        if (isset($this->_published[$path])) {
            return $this->_published[$path][1];
        }
        if (is_string($path) && ($path = realpath($path)) !== false) {
            return $this->baseUrl . '/' . $this->hash($path) . (is_file($path) ? '/' . basename($path) : '');
        } else {
            return false;
        }
    }

    
    protected function hash($path)
    {
        if (is_callable($this->hashCallback)) {
            return call_user_func($this->hashCallback, $path);
        }
        $path = (is_file($path) ? dirname($path) : $path) . filemtime($path);
        return sprintf('%x', crc32($path . Aabc::getVersion()));
    }
}
