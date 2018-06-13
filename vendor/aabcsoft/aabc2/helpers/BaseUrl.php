<?php


namespace aabc\helpers;

use Aabc;
use aabc\base\InvalidParamException;


class BaseUrl
{
    
    public static $urlManager;


    
    public static function toRoute($route, $scheme = false)
    {
        $route = (array) $route;
        $route[0] = static::normalizeRoute($route[0]);

        if ($scheme !== false) {
            return static::getUrlManager()->createAbsoluteUrl($route, is_string($scheme) ? $scheme : null);
        } else {
            return static::getUrlManager()->createUrl($route);
        }
    }

    
    protected static function normalizeRoute($route)
    {
        $route = Aabc::getAlias((string) $route);
        if (strncmp($route, '/', 1) === 0) {
            // absolute route
            return ltrim($route, '/');
        }

        // relative route
        if (Aabc::$app->controller === null) {
            throw new InvalidParamException("Unable to resolve the relative route: $route. No active controller is available.");
        }

        if (strpos($route, '/') === false) {
            // empty or an action ID
            return $route === '' ? Aabc::$app->controller->getRoute() : Aabc::$app->controller->getUniqueId() . '/' . $route;
        } else {
            // relative to module
            return ltrim(Aabc::$app->controller->module->getUniqueId() . '/' . $route, '/');
        }
    }

    
    public static function to($url = '', $scheme = false)
    {
        if (is_array($url)) {
            return static::toRoute($url, $scheme);
        }

        $url = Aabc::getAlias($url);
        if ($url === '') {
            $url = Aabc::$app->getRequest()->getUrl();
        }

        if ($scheme === false) {
            return $url;
        }

        if (static::isRelative($url)) {
            // turn relative URL into absolute
            $url = static::getUrlManager()->getHostInfo() . '/' . ltrim($url, '/');
        }

        return static::ensureScheme($url, $scheme);
    }

    
    public static function ensureScheme($url, $scheme)
    {
        if (static::isRelative($url) || !is_string($scheme)) {
            return $url;
        }

        if (substr($url, 0, 2) === '//') {
            // e.g. //example.com/path/to/resource
            return $scheme === '' ? $url : "$scheme:$url";
        }

        if (($pos = strpos($url, '://')) !== false) {
            if ($scheme === '') {
                $url = substr($url, $pos + 1);
            } else {
                $url = $scheme . substr($url, $pos);
            }
        }

        return $url;
    }

    
    public static function base($scheme = false)
    {
        $url = static::getUrlManager()->getBaseUrl();
        if ($scheme !== false) {
            $url = static::getUrlManager()->getHostInfo() . $url;
            $url = static::ensureScheme($url, $scheme);
        }

        return $url;
    }

    
    public static function remember($url = '', $name = null)
    {
        $url = static::to($url);

        if ($name === null) {
            Aabc::$app->getUser()->setReturnUrl($url);
        } else {
            Aabc::$app->getSession()->set($name, $url);
        }
    }

    
    public static function previous($name = null)
    {
        if ($name === null) {
            return Aabc::$app->getUser()->getReturnUrl();
        } else {
            return Aabc::$app->getSession()->get($name);
        }
    }

    
    public static function canonical()
    {
        $params = Aabc::$app->controller->actionParams;
        $params[0] = Aabc::$app->controller->getRoute();

        return static::getUrlManager()->createAbsoluteUrl($params);
    }

    
    public static function home($scheme = false)
    {
        $url = Aabc::$app->getHomeUrl();

        if ($scheme !== false) {
            $url = static::getUrlManager()->getHostInfo() . $url;
            $url = static::ensureScheme($url, $scheme);
        }

        return $url;
    }

    
    public static function isRelative($url)
    {
        return strncmp($url, '//', 2) && strpos($url, '://') === false;
    }

    
    public static function current(array $params = [], $scheme = false)
    {
        $currentParams = Aabc::$app->getRequest()->getQueryParams();
        $currentParams[0] = '/' . Aabc::$app->controller->getRoute();
        $route = ArrayHelper::merge($currentParams, $params);
        return static::toRoute($route, $scheme);
    }

    
    protected static function getUrlManager()
    {
        return static::$urlManager ?: Aabc::$app->getUrlManager();
    }
}
