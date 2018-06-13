<?php


namespace aabc\base;

use Aabc;
use aabc\helpers\FileHelper;


class Theme extends Component
{
    
    public $pathMap;

    private $_baseUrl;


    
    public function getBaseUrl()
    {
        return $this->_baseUrl;
    }

    
    public function setBaseUrl($url)
    {
        $this->_baseUrl = rtrim(Aabc::getAlias($url), '/');
    }

    private $_basePath;

    
    public function getBasePath()
    {
        return $this->_basePath;
    }

    
    public function setBasePath($path)
    {
        $this->_basePath = Aabc::getAlias($path);
    }

    
    public function applyTo($path)
    {
        $pathMap = $this->pathMap;
        if (empty($pathMap)) {
            if (($basePath = $this->getBasePath()) === null) {
                throw new InvalidConfigException('The "basePath" property must be set.');
            }
            $pathMap = [Aabc::$app->getBasePath() => [$basePath]];
        }

        $path = FileHelper::normalizePath($path);

        foreach ($pathMap as $from => $tos) {
            $from = FileHelper::normalizePath(Aabc::getAlias($from)) . DIRECTORY_SEPARATOR;
            if (strpos($path, $from) === 0) {
                $n = strlen($from);
                foreach ((array) $tos as $to) {
                    $to = FileHelper::normalizePath(Aabc::getAlias($to)) . DIRECTORY_SEPARATOR;
                    $file = $to . substr($path, $n);
                    if (is_file($file)) {
                        return $file;
                    }
                }
            }
        }

        return $path;
    }

    
    public function getUrl($url)
    {
        if (($baseUrl = $this->getBaseUrl()) !== null) {
            return $baseUrl . '/' . ltrim($url, '/');
        } else {
            throw new InvalidConfigException('The "baseUrl" property must be set.');
        }
    }

    
    public function getPath($path)
    {
        if (($basePath = $this->getBasePath()) !== null) {
            return $basePath . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
        } else {
            throw new InvalidConfigException('The "basePath" property must be set.');
        }
    }
}
