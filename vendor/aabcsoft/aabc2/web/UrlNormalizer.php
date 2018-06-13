<?php


namespace aabc\web;

use Aabc;
use aabc\base\Object;
use aabc\base\InvalidConfigException;


class UrlNormalizer extends Object
{
    
    const ACTION_REDIRECT_PERMANENT = 301;
    
    const ACTION_REDIRECT_TEMPORARY = 302;
    
    const ACTION_NOT_FOUND = 404;

    
    public $collapseSlashes = true;
    
    public $normalizeTrailingSlash = true;
    
    public $action = self::ACTION_REDIRECT_PERMANENT;


    
    public function normalizeRoute($route)
    {
        if ($this->action === null) {
            return $route;
        } elseif ($this->action === static::ACTION_REDIRECT_PERMANENT || $this->action === static::ACTION_REDIRECT_TEMPORARY) {
            throw new UrlNormalizerRedirectException([$route[0]] + $route[1], $this->action);
        } elseif ($this->action === static::ACTION_NOT_FOUND) {
            throw new NotFoundHttpException(Aabc::t('aabc', 'Page not found.'));
        } elseif (is_callable($this->action)) {
            return call_user_func($this->action, $route, $this);
        }

        throw new InvalidConfigException('Invalid normalizer action.');
    }

    
    public function normalizePathInfo($pathInfo, $suffix, &$normalized = false)
    {
        if (empty($pathInfo)) {
            return $pathInfo;
        }

        $sourcePathInfo = $pathInfo;
        if ($this->collapseSlashes) {
            $pathInfo = $this->collapseSlashes($pathInfo);
        }

        if ($this->normalizeTrailingSlash === true) {
            $pathInfo = $this->normalizeTrailingSlash($pathInfo, $suffix);
        }

        $normalized = $sourcePathInfo !== $pathInfo;

        return $pathInfo;
    }

    
    protected function collapseSlashes($pathInfo)
    {
        return ltrim(preg_replace('#/{2,}#', '/', $pathInfo), '/');
    }

    
    protected function normalizeTrailingSlash($pathInfo, $suffix)
    {
        if (substr($suffix, -1) === '/' && substr($pathInfo, -1) !== '/') {
            $pathInfo .= '/';
        } elseif (substr($suffix, -1) !== '/' && substr($pathInfo, -1) === '/') {
            $pathInfo = rtrim($pathInfo, '/');
        }

        return $pathInfo;
    }
}
