<?php

namespace Codeception\Module;

use Codeception\Lib\Interfaces\API;
use Codeception\Module as CodeceptionModule;
use Codeception\Lib\Framework;
use Codeception\Exception\ModuleConfigException;
use Codeception\Exception\ModuleRequireException;
use Codeception\TestInterface;


class XMLRPC extends CodeceptionModule implements API
{
    protected $config = ['url' => ""];

    
    public $client = null;
    public $is_functional = false;

    public $headers = [];
    public $params = [];
    public $response = "";

    public function _initialize()
    {
        if (!function_exists('xmlrpc_encode_request')) {
            throw new ModuleRequireException(__CLASS__, "XMLRPC module requires installed php_xmlrpc extension");
        }
        parent::_initialize();
    }

    public function _before(TestInterface $test)
    {
        if (!$this->client) {
            if (!strpos($this->config['url'], '://')) {
                // not valid url
                foreach ($this->getModules() as $module) {
                    if ($module instanceof Framework) {
                        $this->client = $module->client;
                        $this->is_functional = true;
                        break;
                    }
                }
            } else {
                if (!$this->hasModule('PhpBrowser')) {
                    throw new ModuleConfigException(
                        __CLASS__,
                        "For XMLRPC testing via HTTP please enable PhpBrowser module"
                    );
                }
                $this->client = $this->getModule('PhpBrowser')->client;
            }
            if (!$this->client) {
                throw new ModuleConfigException(
                    __CLASS__,
                    "Client for XMLRPC requests not initialized.\n"
                    . "Provide either PhpBrowser module, or a framework module which shares FrameworkInterface"
                );
            }
        }

        $this->headers = [];
        $this->params = [];
        $this->response = '';

        $this->client->setServerParameters([]);
    }

    
    public function haveHttpHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    
    public function seeResponseCodeIs($num)
    {
        \PHPUnit_Framework_Assert::assertEquals($num, $this->client->getInternalResponse()->getStatus());
    }

    
    public function seeResponseIsXMLRPC()
    {
        $result = xmlrpc_decode($this->response);
        \PHPUnit_Framework_Assert::assertNotNull($result, 'Invalid response document returned from XmlRpc server');
    }

    
    public function sendXMLRPCMethodCall($methodName, $parameters = [])
    {
        if (!array_key_exists('Content-Type', $this->headers)) {
            $this->headers['Content-Type'] = 'text/xml';
        }

        foreach ($this->headers as $header => $val) {
            $this->client->setServerParameter("HTTP_$header", $val);
        }

        $url = $this->config['url'];

        if (is_array($parameters)) {
            $parameters = $this->scalarizeArray($parameters);
        }

        $requestBody = xmlrpc_encode_request($methodName, array_values($parameters));

        $this->debugSection('Request', $url . PHP_EOL . $requestBody);
        $this->client->request('POST', $url, [], [], [], $requestBody);

        $this->response = $this->client->getInternalResponse()->getContent();
        $this->debugSection('Response', $this->response);
    }
}
