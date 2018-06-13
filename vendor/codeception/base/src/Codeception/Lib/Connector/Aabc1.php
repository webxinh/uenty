<?php
namespace Codeception\Lib\Connector;

use Codeception\Util\Stub;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response;

class Aabc1 extends Client
{
    use Shared\PhpSuperGlobalsConverter;
    
    public $url;

    
    public $appSettings;

    
    public $appPath;

    
    private $headers;

    
    public function doRequest($request)
    {
        $this->headers = array();
        $_COOKIE        = array_merge($_COOKIE, $request->getCookies());
        $_SERVER        = array_merge($_SERVER, $request->getServer());
        $_FILES         = $this->remapFiles($request->getFiles());
        $_REQUEST       = $this->remapRequestParameters($request->getParameters());
        $_POST          = $_GET = array();

        if (strtoupper($request->getMethod()) == 'GET') {
            $_GET = $_REQUEST;
        } else {
            $_POST = $_REQUEST;
        }

        // Parse url parts
        $uriPath = trim(parse_url($request->getUri(), PHP_URL_PATH), '/');
        $uriQuery = ltrim(parse_url($request->getUri(), PHP_URL_QUERY), '?');
        $scriptName = trim(parse_url($this->url, PHP_URL_PATH), '/');
        if (!empty($uriQuery)) {
            $uriPath .= "?{$uriQuery}";

            parse_str($uriQuery, $params);
            foreach ($params as $k => $v) {
                $_GET[$k] = $v;
            }
        }

        // Add script name to request if none
        if (strpos($uriPath, $scriptName) === false) {
            $uriPath = "/{$scriptName}/{$uriPath}";
        }

        // Add forward slash if not exists
        if (strpos($uriPath, '/') !== 0) {
            $uriPath = "/{$uriPath}";
        }

        $_SERVER['REQUEST_METHOD'] = strtoupper($request->getMethod());
        $_SERVER['REQUEST_URI'] = $uriPath;

        
        $_SERVER['SCRIPT_NAME'] = "/{$scriptName}";
        $_SERVER['SCRIPT_FILENAME'] = $this->appPath;

        ob_start();
        \Aabc::setApplication(null);
        \Aabc::createApplication($this->appSettings['class'], $this->appSettings['config']);

        $app = \Aabc::app();
        // disabling logging. Logs slow down test execution
        if ($app->hasComponent('log')) {
            foreach ($app->getComponent('log')->routes as $route) {
                $route->enabled = false;
            }
        }

        if ($app->hasComponent('session')) { // disable regenerate id in session
            $app->setComponent('session', Stub::make('CHttpSession', ['regenerateID' => false]));
        }

        $app->onEndRequest->add([$this, 'setHeaders']);
        $app->run();

        if ($app->hasComponent('db')) {
            // close connection
            $app->getDb()->setActive(false);
            // cleanup metadata cache
            $property = new \ReflectionProperty('CActiveRecord', '_md');
            $property->setAccessible(true);
            $property->setValue([]);
        }

        $content = ob_get_clean();

        $headers = $this->getHeaders();
        $statusCode = 200;
        foreach ($headers as $header => $val) {
            if ($header == 'Location') {
                $statusCode = 302;
            }
        }

        $response = new Response($content, $statusCode, $this->getHeaders());

        return $response;
    }

    
    public function setHeaders()
    {
        $this->headers = \Aabc::app()->request->getAllHeaders();
    }

    
    public function getHeaders()
    {
        return $this->headers;
    }
}
