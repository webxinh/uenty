<?php


namespace aabc\web;

use Aabc;
use aabc\helpers\Url;
use aabc\base\InvalidRouteException;


class Application extends \aabc\base\Application
{
    
    public $defaultRoute = 'site';
    
    public $catchAll;
    
    public $controller;


    
    protected function bootstrap()
    {
        $request = $this->getRequest();
        Aabc::setAlias('@webroot', dirname($request->getScriptFile()));
        Aabc::setAlias('@web', $request->getBaseUrl());

        parent::bootstrap();
    }

    
    public function handleRequest($request)
    {
        if (empty($this->catchAll)) {
            try {
                list ($route, $params) = $request->resolve();
            } catch (UrlNormalizerRedirectException $e) {
                $url = $e->url;
                if (is_array($url)) {
                    if (isset($url[0])) {
                        // ensure the route is absolute
                        $url[0] = '/' . ltrim($url[0], '/');
                    }
                    $url += $request->getQueryParams();
                }
                return $this->getResponse()->redirect(Url::to($url, $e->scheme), $e->statusCode);
            }
        } else {
            $route = $this->catchAll[0];
            $params = $this->catchAll;
            unset($params[0]);
        }
        try {
            Aabc::trace("Route requested: '$route'", __METHOD__);
            $this->requestedRoute = $route;
            $result = $this->runAction($route, $params);
            if ($result instanceof Response) {
                return $result;
            } else {
                $response = $this->getResponse();
                if ($result !== null) {
                    $response->data = $result;
                }

                return $response;
            }
        } catch (InvalidRouteException $e) {
            throw new NotFoundHttpException(Aabc::t('aabc', 'Page not found.'), $e->getCode(), $e);
        }
    }

    private $_homeUrl;

    
    public function getHomeUrl()
    {
        if ($this->_homeUrl === null) {
            if ($this->getUrlManager()->showScriptName) {
                return $this->getRequest()->getScriptUrl();
            } else {
                return $this->getRequest()->getBaseUrl() . '/';
            }
        } else {
            return $this->_homeUrl;
        }
    }

    
    public function setHomeUrl($value)
    {
        $this->_homeUrl = $value;
    }

    
    public function getErrorHandler()
    {
        return $this->get('errorHandler');
    }

    
    public function getRequest()
    {
        return $this->get('request');
    }

    
    public function getResponse()
    {
        return $this->get('response');
    }

    
    public function getSession()
    {
        return $this->get('session');
    }

    
    public function getUser()
    {
        return $this->get('user');
    }

    
    public function coreComponents()
    {
        return array_merge(parent::coreComponents(), [
            'request' => ['class' => 'aabc\web\Request'],
            'response' => ['class' => 'aabc\web\Response'],
            'session' => ['class' => 'aabc\web\Session'],
            'user' => ['class' => 'aabc\web\User'],
            'errorHandler' => ['class' => 'aabc\web\ErrorHandler'],
        ]);
    }
}
