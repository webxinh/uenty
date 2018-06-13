<?php
namespace Codeception\Module;

use Codeception\Lib\Interfaces\API;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\Notification;
use Codeception\Module as CodeceptionModule;
use Codeception\TestInterface;
use Codeception\Exception\ModuleException;
use Codeception\Exception\ModuleRequireException;
use Codeception\Lib\Framework;
use Codeception\Lib\InnerBrowser;
use Codeception\Util\Soap as SoapUtils;
use Codeception\Util\XmlStructure;


class SOAP extends CodeceptionModule implements DependsOnModule
{
    protected $config = [
        'schema' => "",
        'schema_url' => 'http://schemas.xmlsoap.org/soap/envelope/',
        'framework_collect_buffer' => true
    ];

    protected $requiredFields = ['endpoint'];

    protected $dependencyMessage = <<<EOF
Example using PhpBrowser as backend for SOAP module.
--
modules:
    enabled:
        - SOAP:
            depends: PhpBrowser
--
Framework modules can be used as well for functional testing of SOAP API.
EOF;

    
    public $client = null;
    public $isFunctional = false;

    
    public $xmlRequest = null;
    
    public $xmlResponse = null;

    
    protected $xmlStructure = null;

    
    protected $connectionModule;

    public function _before(TestInterface $test)
    {
        $this->client = &$this->connectionModule->client;
        $this->buildRequest();
        $this->xmlResponse = null;
        $this->xmlStructure = null;
    }

    protected function onReconfigure()
    {
        $this->buildRequest();
        $this->xmlResponse = null;
        $this->xmlStructure = null;
    }

    public function _depends()
    {
        return ['Codeception\Lib\InnerBrowser' => $this->dependencyMessage];
    }

    public function _inject(InnerBrowser $connectionModule)
    {
        $this->connectionModule = $connectionModule;
        if ($connectionModule instanceof Framework) {
            $this->isFunctional = true;
        }
    }

    private function getClient()
    {
        if (!$this->client) {
            throw new ModuleRequireException($this, "Connection client is not available.");
        }
        return $this->client;
    }

    private function getXmlResponse()
    {
        if (!$this->xmlResponse) {
            throw new ModuleException($this, "No XML response, use `\$I->sendSoapRequest` to receive it");
        }
        return $this->xmlResponse;
    }

    private function getXmlStructure()
    {
        if (!$this->xmlStructure) {
            $this->xmlStructure = new XmlStructure($this->getXmlResponse());
        }
        return $this->xmlStructure;
    }

    
    public function haveSoapHeader($header, $params = [])
    {
        $soap_schema_url = $this->config['schema_url'];
        $xml = $this->xmlRequest;
        $xmlHeader = $xml->documentElement->getElementsByTagNameNS($soap_schema_url, 'Header')->item(0);
        $headerEl = $xml->createElement($header);
        SoapUtils::arrayToXml($xml, $headerEl, $params);
        $xmlHeader->appendChild($headerEl);
    }

    
    public function sendSoapRequest($action, $body = "")
    {
        $soap_schema_url = $this->config['schema_url'];
        $xml = $this->xmlRequest;
        $call = $xml->createElement('ns:' . $action);
        if ($body) {
            $bodyXml = SoapUtils::toXml($body);
            if ($bodyXml->hasChildNodes()) {
                foreach ($bodyXml->childNodes as $bodyChildNode) {
                    $bodyNode = $xml->importNode($bodyChildNode, true);
                    $call->appendChild($bodyNode);
                }
            }
        }

        $xmlBody = $xml->getElementsByTagNameNS($soap_schema_url, 'Body')->item(0);

        // cleanup if body already set
        foreach ($xmlBody->childNodes as $node) {
            $xmlBody->removeChild($node);
        }

        $xmlBody->appendChild($call);
        $this->debugSection("Request", $req = $xml->C14N());

        if ($this->isFunctional && $this->config['framework_collect_buffer']) {
            $response = $this->processInternalRequest($action, $req);
        } else {
            $response = $this->processExternalRequest($action, $req);
        }

        $this->debugSection("Response", (string) $response);
        $this->xmlResponse = SoapUtils::toXml($response);
        $this->xmlStructure = null;
    }

    
    public function seeSoapResponseEquals($xml)
    {
        $xml = SoapUtils::toXml($xml);
        $this->assertEquals($xml->C14N(), $this->getXmlResponse()->C14N());
    }

    
    public function seeSoapResponseIncludes($xml)
    {
        $xml = $this->canonicalize($xml);
        $this->assertContains($xml, $this->getXmlResponse()->C14N(), "found in XML Response");
    }


    
    public function dontSeeSoapResponseEquals($xml)
    {
        $xml = SoapUtils::toXml($xml);
        \PHPUnit_Framework_Assert::assertXmlStringNotEqualsXmlString($xml->C14N(), $this->getXmlResponse()->C14N());
    }


    
    public function dontSeeSoapResponseIncludes($xml)
    {
        $xml = $this->canonicalize($xml);
        $this->assertNotContains($xml, $this->getXmlResponse()->C14N(), "found in XML Response");
    }

    
    public function seeSoapResponseContainsStructure($xml)
    {
        $xml = SoapUtils::toXml($xml);
        $this->debugSection("Structure", $xml->saveXML());
        $this->assertTrue((bool)$this->getXmlStructure()->matchXmlStructure($xml), "this structure is in response");
    }

    
    public function dontSeeSoapResponseContainsStructure($xml)
    {
        $xml = SoapUtils::toXml($xml);
        $this->debugSection("Structure", $xml->saveXML());
        $this->assertFalse((bool)$this->getXmlStructure()->matchXmlStructure($xml), "this structure is in response");
    }

    
    public function seeSoapResponseContainsXPath($xpath)
    {
        $this->assertTrue($this->getXmlStructure()->matchesXpath($xpath));
    }

    
    public function dontSeeSoapResponseContainsXPath($xpath)
    {
        $this->assertFalse($this->getXmlStructure()->matchesXpath($xpath));
    }


    
    public function seeSoapResponseCodeIs($code)
    {
        $this->assertEquals(
            $code,
            $this->client->getInternalResponse()->getStatus(),
            "soap response code matches expected"
        );
    }

    
    public function seeResponseCodeIs($code)
    {
        Notification::deprecate('SOAP::seeResponseCodeIs deprecated in favor of seeSoapResponseCodeIs', 'SOAP Module');
        $this->seeSoapResponseCodeIs($code);
    }

    
    public function grabTextContentFrom($cssOrXPath)
    {
        $el = $this->getXmlStructure()->matchElement($cssOrXPath);
        return $el->textContent;
    }

    
    public function grabAttributeFrom($cssOrXPath, $attribute)
    {
        $el = $this->getXmlStructure()->matchElement($cssOrXPath);
        if (!$el->hasAttribute($attribute)) {
            $this->fail("Attribute not found in element matched by '$cssOrXPath'");
        }
        return $el->getAttribute($attribute);
    }

    protected function getSchema()
    {
        return $this->config['schema'];
    }

    protected function canonicalize($xml)
    {
        return SoapUtils::toXml($xml)->C14N();
    }

    
    protected function buildRequest()
    {
        $soap_schema_url = $this->config['schema_url'];
        $xml = new \DOMDocument();
        $root = $xml->createElement('soapenv:Envelope');
        $xml->appendChild($root);
        $root->setAttribute('xmlns:ns', $this->getSchema());
        $root->setAttribute('xmlns:soapenv', $soap_schema_url);
        $body = $xml->createElementNS($soap_schema_url, 'soapenv:Body');
        $header = $xml->createElementNS($soap_schema_url, 'soapenv:Header');
        $root->appendChild($header);
        $root->appendChild($body);
        $this->xmlRequest = $xml;
        return $xml;
    }

    protected function processRequest($action, $body)
    {
        $this->getClient()->request(
            'POST',
            $this->config['endpoint'],
            [],
            [],
            [
                'HTTP_Content-Type' => 'text/xml; charset=UTF-8',
                'HTTP_Content-Length' => strlen($body),
                'HTTP_SOAPAction' => isset($this->config['SOAPAction']) ? $this->config['SOAPAction'] : $action
            ],
            $body
        );
    }

    protected function processInternalRequest($action, $body)
    {
        ob_start();
        try {
            $this->getClient()->setServerParameter('HTTP_HOST', 'localhost');
            $this->processRequest($action, $body);
        } catch (\ErrorException $e) {
            // Zend_Soap outputs warning as an exception
            if (strpos($e->getMessage(), 'Warning: Cannot modify header information') === false) {
                ob_end_clean();
                throw $e;
            }
        }
        $response = ob_get_contents();
        ob_end_clean();
        return $response;
    }

    protected function processExternalRequest($action, $body)
    {
        $this->processRequest($action, $body);
        return $this->client->getInternalResponse()->getContent();
    }
}
