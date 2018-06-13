<?php
namespace Codeception\Lib\Connector;

use Codeception\Lib\Connector\Aabc2\Logger;
use Codeception\Lib\Connector\Aabc2\TestMailer;
use Codeception\Util\Debug;
use Codeception\Util\Stub;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\Response;
use Aabc;
use aabc\base\ExitException;
use aabc\web\HttpException;
use aabc\web\Response as AabcResponse;

class Aabc2 extends Client
{
    use Shared\PhpSuperGlobalsConverter;

    
    public $configFile;

    public $defaultServerVars = [];

    
    public $headers;
    public $statusCode;

    
    private $app;

    
    public static $db; // remember the db instance

    
    public static $mailer;

    
    public function getApplication()
    {
        if (!isset($this->app)) {
            $this->startApp();
        }
        return $this->app;
    }

    public function resetApplication()
    {
        if ($this->app->db !== null && $this->app->db->transaction !== null) {
            $this->app->db->transaction->rollBack();
        }
        $this->app = null;
    }

    public function startApp()
    {
        $config = require($this->configFile);
        if (!isset($config['class'])) {
            $config['class'] = 'aabc\web\Application';
        }
        
        $this->app = Aabc::createObject($config);
        $this->persistDb();
        $this->mockMailer($config);
        \Aabc::setLogger(new Logger());
    }

    public function resetPersistentVars()
    {
        static::$db = null;
        static::$mailer = null;
        \aabc\web\UploadedFile::reset();
    }

    
    public function doRequest($request)
    {
        $_COOKIE = $request->getCookies();
        $_SERVER = $request->getServer();
        $this->restoreServerVars();
        $_FILES = $this->remapFiles($request->getFiles());
        $_REQUEST = $this->remapRequestParameters($request->getParameters());
        $_POST = $_GET = [];

        if (strtoupper($request->getMethod()) === 'GET') {
            $_GET = $_REQUEST;
        } else {
            $_POST = $_REQUEST;
        }

        $uri = $request->getUri();

        $pathString = parse_url($uri, PHP_URL_PATH);
        $queryString = parse_url($uri, PHP_URL_QUERY);
        $_SERVER['REQUEST_URI'] = $queryString === null ? $pathString : $pathString . '?' . $queryString;
        $_SERVER['REQUEST_METHOD'] = strtoupper($request->getMethod());

        parse_str($queryString, $params);
        foreach ($params as $k => $v) {
            $_GET[$k] = $v;
        }

        $app = $this->getApplication();

        $app->getResponse()->on(AabcResponse::EVENT_AFTER_PREPARE, [$this, 'processResponse']);

        // disabling logging. Logs are slowing test execution down
        foreach ($app->log->targets as $target) {
            $target->enabled = false;
        }

        $this->headers    = array();
        $this->statusCode = null;

        ob_start();

        $aabcRequest = $app->getRequest();
        if ($request->getContent() !== null) {
            $aabcRequest->setRawBody($request->getContent());
            $aabcRequest->setBodyParams(null);
        } else {
            $aabcRequest->setRawBody(null);
            $aabcRequest->setBodyParams($_POST);
        }
        $aabcRequest->setQueryParams($_GET);

        try {
            $app->handleRequest($aabcRequest)->send();
        } catch (\Exception $e) {
            if ($e instanceof HttpException) {
                // Don't discard output and pass exception handling to Aabc to be able
                // to expect error response codes in tests.
                $app->errorHandler->discardExistingOutput = false;
                $app->errorHandler->handleException($e);
            } elseif (!$e instanceof ExitException) {
                // for exceptions not related to Http, we pass them to Codeception
                $this->resetApplication();
                throw $e;
            }
        }

        $content = ob_get_clean();

        // catch "location" header and display it in debug, otherwise it would be handled
        // by symfony browser-kit and not displayed.
        if (isset($this->headers['location'])) {
            Debug::debug("[Headers] " . json_encode($this->headers));
        }

        $this->resetApplication();

        return new Response($content, $this->statusCode, $this->headers);
    }

    protected function revertErrorHandler()
    {
        $handler = new ErrorHandler();
        set_error_handler(array($handler, 'errorHandler'));
    }


    public function restoreServerVars()
    {
        $this->server = $this->defaultServerVars;
        foreach ($this->server as $key => $value) {
            $_SERVER[$key] = $value;
        }
    }

    public function processResponse($event)
    {
        
        $response = $event->sender;
        $request = Aabc::$app->getRequest();
        $this->headers = $response->getHeaders()->toArray();
        $response->getHeaders()->removeAll();
        $this->statusCode = $response->getStatusCode();
        $cookies = $response->getCookies();

        if ($request->enableCookieValidation) {
            $validationKey = $request->cookieValidationKey;
        }

        foreach ($cookies as $cookie) {
            
            $value = $cookie->value;
            if ($cookie->expire != 1 && isset($validationKey)) {
                $data = version_compare(Aabc::getVersion(), '2.0.2', '>')
                    ? [$cookie->name, $cookie->value]
                    : $cookie->value;
                $value = Aabc::$app->security->hashData(serialize($data), $validationKey);
            }
            $c = new Cookie(
                $cookie->name,
                $value,
                $cookie->expire,
                $cookie->path,
                $cookie->domain,
                $cookie->secure,
                $cookie->httpOnly
            );
            $this->getCookieJar()->set($c);
        }
        $cookies->removeAll();
    }

    
    protected function mockMailer($config)
    {
        if (static::$mailer) {
            $this->app->set('mailer', static::$mailer);
            return;
        }
        
        // options that make sense for mailer mock
        $allowedOptions = [
            'htmlLayout',
            'textLayout',
            'messageConfig',
            'messageClass',
            'useFileTransport',
            'fileTransportPath',
            'fileTransportCallback',
            'view',
            'viewPath',
        ];
        
        $mailerConfig = [
            'class' => 'Codeception\Lib\Connector\Aabc2\TestMailer',
        ];
        
        if (isset($config['components']['mailer']) && is_array($config['components']['mailer'])) {
            foreach ($config['components']['mailer'] as $name => $value) {
                if (in_array($name, $allowedOptions, true)) {
                    $mailerConfig[$name] = $value;
                }
            }
        }
        
        $this->app->set('mailer', $mailerConfig);
        static::$mailer = $this->app->get('mailer');
    }

    
    protected function persistDb()
    {
        // always use the same DB connection
        if (static::$db) {
            $this->app->set('db', static::$db);
        } elseif ($this->app->has('db')) {
            static::$db = $this->app->get('db');
        }
    }
}
