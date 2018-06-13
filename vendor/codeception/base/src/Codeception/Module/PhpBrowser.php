<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Lib\Connector\Guzzle6;
use Codeception\Lib\InnerBrowser;
use Codeception\Lib\Interfaces\MultiSession;
use Codeception\Lib\Interfaces\Remote;
use Codeception\Lib\Interfaces\RequiresPackage;
use Codeception\TestInterface;
use Codeception\Util\Uri;
use GuzzleHttp\Client as GuzzleClient;


class PhpBrowser extends InnerBrowser implements Remote, MultiSession, RequiresPackage
{

    private $isGuzzlePsr7;
    protected $requiredFields = ['url'];

    protected $config = [
        'verify' => false,
        'expect' => false,
        'timeout' => 30,
        'curl' => [],
        'refresh_max_interval' => 10,
        'handler' => 'curl',
        'middleware' => null,

        // required defaults (not recommended to change)
        'allow_redirects' => false,
        'http_errors' => false,
        'cookies' => true,
    ];

    protected $guzzleConfigFields = [
        'headers',
        'auth',
        'proxy',
        'verify',
        'cert',
        'query',
        'ssl_key',
        'proxy',
        'expect',
        'version',
        'timeout',
        'connect_timeout'
    ];

    
    public $client;

    
    public $guzzle;

    public function _requires()
    {
        return ['GuzzleHttp\Client' => '"guzzlehttp/guzzle": ">=4.1.4 <7.0"'];
    }

    public function _initialize()
    {
        $this->_initializeSession();
    }

    protected function guessGuzzleConnector()
    {
        if (class_exists('GuzzleHttp\Url')) {
            $this->isGuzzlePsr7 = false;
            return new \Codeception\Lib\Connector\Guzzle();
        }
        $this->isGuzzlePsr7 = true;
        return new \Codeception\Lib\Connector\Guzzle6();
    }

    public function _before(TestInterface $test)
    {
        if (!$this->client) {
            $this->client = $this->guessGuzzleConnector();
        }
        $this->_prepareSession();
    }

    public function _getUrl()
    {
        return $this->config['url'];
    }

    
    public function setHeader($name, $value)
    {
        $this->haveHttpHeader($name, $value);
    }

    public function amHttpAuthenticated($username, $password)
    {
        $this->client->setAuth($username, $password);
    }

    public function amOnUrl($url)
    {
        $host = Uri::retrieveHost($url);
        $this->_reconfigure(['url' => $host]);
        $page = substr($url, strlen($host));
        $this->debugSection('Host', $host);
        $this->amOnPage($page);
    }

    public function amOnSubdomain($subdomain)
    {
        $url = $this->config['url'];
        $url = preg_replace('~(https?:\/\/)(.*\.)(.*\.)~', "$1$3", $url); // removing current subdomain
        $url = preg_replace('~(https?:\/\/)(.*)~', "$1$subdomain.$2", $url); // inserting new
        $this->_reconfigure(['url' => $url]);
    }

    protected function onReconfigure()
    {
        $this->_prepareSession();
    }

    
    public function executeInGuzzle(\Closure $function)
    {
        return $function($this->guzzle);
    }


    public function _getResponseCode()
    {
        return $this->getResponseStatusCode();
    }

    public function _initializeSession()
    {
        // independent sessions need independent cookies
        $this->client = $this->guessGuzzleConnector();
        $this->_prepareSession();
    }

    public function _prepareSession()
    {
        $defaults = array_intersect_key($this->config, array_flip($this->guzzleConfigFields));
        $curlOptions = [];

        foreach ($this->config['curl'] as $key => $val) {
            if (defined($key)) {
                $curlOptions[constant($key)] = $val;
            }
        }

        $this->setCookiesFromOptions();

        if ($this->isGuzzlePsr7) {
            $defaults['base_uri'] = $this->config['url'];
            $defaults['curl'] = $curlOptions;
            $handler = Guzzle6::createHandler($this->config['handler']);
            if ($handler && is_array($this->config['middleware'])) {
                foreach ($this->config['middleware'] as $middleware) {
                    $handler->push($middleware);
                }
            }
            $defaults['handler'] = $handler;
            $this->guzzle = new GuzzleClient($defaults);
        } else {
            $defaults['config']['curl'] = $curlOptions;
            $this->guzzle = new GuzzleClient(['base_url' => $this->config['url'], 'defaults' => $defaults]);
            $this->client->setBaseUri($this->config['url']);
        }

        $this->client->setRefreshMaxInterval($this->config['refresh_max_interval']);
        $this->client->setClient($this->guzzle);
    }

    public function _backupSession()
    {
        return [
            'client' => $this->client,
            'guzzle' => $this->guzzle,
            'crawler' => $this->crawler
        ];
    }

    public function _loadSession($session)
    {
        foreach ($session as $key => $val) {
            $this->$key = $val;
        }
    }

    public function _closeSession($session)
    {
        unset($session);
    }
}
