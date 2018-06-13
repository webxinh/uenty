<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleException;
use Codeception\Lib\Interfaces\ConflictsWithModule;
use Codeception\Module as CodeceptionModule;
use Codeception\PHPUnit\Constraint\JsonContains;
use Codeception\PHPUnit\Constraint\JsonType as JsonTypeConstraint;
use Codeception\TestInterface;
use Codeception\Lib\Interfaces\API;
use Codeception\Lib\Framework;
use Codeception\Lib\InnerBrowser;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\Interfaces\PartedModule;
use Codeception\Util\JsonArray;
use Codeception\Util\JsonType;
use Codeception\Util\XmlStructure;
use Symfony\Component\BrowserKit\Cookie;
use Codeception\Util\Soap as XmlUtils;


class REST extends CodeceptionModule implements DependsOnModule, PartedModule, API, ConflictsWithModule
{
    protected $config = [
        'url' => ''
    ];

    protected $dependencyMessage = <<<EOF
Example configuring PhpBrowser as backend for REST module.
--
modules:
    enabled:
        - REST:
            depends: PhpBrowser
            url: http://localhost/api/
--
Framework modules can be used for testing of API as well.
EOF;

    
    public $client = null;
    public $isFunctional = false;

    
    protected $connectionModule;

    public $params = [];
    public $response = "";

    public function _before(TestInterface $test)
    {
        $this->client = &$this->connectionModule->client;
        $this->resetVariables();
    }

    protected function resetVariables()
    {
        $this->params = [];
        $this->response = "";
        $this->connectionModule->headers = [];
    }

    public function _conflicts()
    {
        return 'Codeception\Lib\Interfaces\API';
    }

    public function _depends()
    {
        return ['Codeception\Lib\InnerBrowser' => $this->dependencyMessage];
    }

    public function _parts()
    {
        return ['xml', 'json'];
    }

    public function _inject(InnerBrowser $connection)
    {
        $this->connectionModule = $connection;
        if ($this->connectionModule instanceof Framework) {
            $this->isFunctional = true;
        }
        if ($this->connectionModule instanceof PhpBrowser) {
            if (!$this->connectionModule->_getConfig('url')) {
                $this->connectionModule->_setConfig(['url' => $this->config['url']]);
            }
        }
    }

    protected function getRunningClient()
    {
        if ($this->client->getInternalRequest() === null) {
            throw new ModuleException($this, "Response is empty. Use `\$I->sendXXX()` methods to send HTTP request");
        }
        return $this->client;
    }

    
    public function haveHttpHeader($name, $value)
    {
        $this->connectionModule->haveHttpHeader($name, $value);
    }

    
    public function deleteHeader($name)
    {
        $this->connectionModule->deleteHeader($name);
    }

    
    public function seeHttpHeader($name, $value = null)
    {
        if ($value !== null) {
            $this->assertEquals(
                $value,
                $this->getRunningClient()->getInternalResponse()->getHeader($name)
            );
            return;
        }
        $this->assertNotNull($this->getRunningClient()->getInternalResponse()->getHeader($name));
    }

    
    public function dontSeeHttpHeader($name, $value = null)
    {
        if ($value !== null) {
            $this->assertNotEquals(
                $value,
                $this->getRunningClient()->getInternalResponse()->getHeader($name)
            );
            return;
        }
        $this->assertNull($this->getRunningClient()->getInternalResponse()->getHeader($name));
    }

    
    public function seeHttpHeaderOnce($name)
    {
        $headers = $this->getRunningClient()->getInternalResponse()->getHeader($name, false);
        $this->assertEquals(1, count($headers));
    }

    
    public function grabHttpHeader($name, $first = true)
    {
        return $this->getRunningClient()->getInternalResponse()->getHeader($name, $first);
    }

    
    public function amHttpAuthenticated($username, $password)
    {
        if ($this->isFunctional) {
            $this->client->setServerParameter('PHP_AUTH_USER', $username);
            $this->client->setServerParameter('PHP_AUTH_PW', $password);
        } else {
            $this->client->setAuth($username, $password);
        }
    }

    
    public function amDigestAuthenticated($username, $password)
    {
        $this->client->setAuth($username, $password, 'digest');
    }

    
    public function amBearerAuthenticated($accessToken)
    {
        $this->haveHttpHeader('Authorization', 'Bearer ' . $accessToken);
    }

    
    public function sendPOST($url, $params = [], $files = [])
    {
        $this->execute('POST', $url, $params, $files);
    }

    
    public function sendHEAD($url, $params = [])
    {
        $this->execute('HEAD', $url, $params);
    }

    
    public function sendOPTIONS($url, $params = [])
    {
        $this->execute('OPTIONS', $url, $params);
    }

    
    public function sendGET($url, $params = [])
    {
        $this->execute('GET', $url, $params);
    }

    
    public function sendPUT($url, $params = [], $files = [])
    {
        $this->execute('PUT', $url, $params, $files);
    }

    
    public function sendPATCH($url, $params = [], $files = [])
    {
        $this->execute('PATCH', $url, $params, $files);
    }

    
    public function sendDELETE($url, $params = [], $files = [])
    {
        $this->execute('DELETE', $url, $params, $files);
    }

    
    private function setHeaderLink(array $linkEntries)
    {
        $values = [];
        foreach ($linkEntries as $linkEntry) {
            \PHPUnit_Framework_Assert::assertArrayHasKey(
                'uri',
                $linkEntry,
                'linkEntry should contain property "uri"'
            );
            \PHPUnit_Framework_Assert::assertArrayHasKey(
                'link-param',
                $linkEntry,
                'linkEntry should contain property "link-param"'
            );
            $values[] = $linkEntry['uri'] . '; ' . $linkEntry['link-param'];
        }

        $this->haveHttpHeader('Link', implode(', ', $values));
    }

    
    public function sendLINK($url, array $linkEntries)
    {
        $this->setHeaderLink($linkEntries);
        $this->execute('LINK', $url);
    }

    
    public function sendUNLINK($url, array $linkEntries)
    {
        $this->setHeaderLink($linkEntries);
        $this->execute('UNLINK', $url);
    }

    protected function execute($method, $url, $parameters = [], $files = [])
    {
        // allow full url to be requested
        if (strpos($url, '://') === false) {
            $url = $this->config['url'] . $url;
        }

        $this->params = $parameters;

        $parameters = $this->encodeApplicationJson($method, $parameters);

        if (is_array($parameters) || $method === 'GET') {
            if (!empty($parameters) && $method === 'GET') {
                if (strpos($url, '?') !== false) {
                    $url .= '&';
                } else {
                    $url .= '?';
                }
                $url .= http_build_query($parameters);
            }
            if ($method == 'GET') {
                $this->debugSection("Request", "$method $url");
                $files = [];
            } else {
                $this->debugSection("Request", "$method $url " . json_encode($parameters));
                $files = $this->formatFilesArray($files);
            }
            $this->response = (string)$this->connectionModule->_request($method, $url, $parameters, $files);
        } else {
            $requestData = $parameters;
            if (!ctype_print($requestData) && false === mb_detect_encoding($requestData, mb_detect_order(), true)) {
                // if the request data has non-printable bytes and it is not a valid unicode string, reformat the
                // display string to signify the presence of request data
                $requestData = '[binary-data length:' . strlen($requestData) . ' md5:' . md5($requestData) . ']';
            }
            $this->debugSection("Request", "$method $url " . $requestData);
            $this->response = (string)$this->connectionModule->_request($method, $url, [], $files, [], $parameters);
        }
        $this->debugSection("Response", $this->response);
    }

    protected function encodeApplicationJson($method, $parameters)
    {
        if ($method !== 'GET' && array_key_exists('Content-Type', $this->connectionModule->headers)
            && ($this->connectionModule->headers['Content-Type'] === 'application/json'
                || preg_match('!^application/.+\+json$!', $this->connectionModule->headers['Content-Type'])
            )
        ) {
            if ($parameters instanceof \JsonSerializable) {
                return json_encode($parameters);
            }
            if (is_array($parameters) || $parameters instanceof \ArrayAccess) {
                $parameters = $this->scalarizeArray($parameters);
                return json_encode($parameters);
            }
        }
        return $parameters;
    }

    private function formatFilesArray(array $files)
    {
        foreach ($files as $name => $value) {
            if (is_string($value)) {
                $this->checkFileBeforeUpload($value);

                $files[$name] = [
                    'name' => basename($value),
                    'tmp_name' => $value,
                    'size' => filesize($value),
                    'type' => $this->getFileType($value),
                    'error' => 0,
                ];
                continue;
            } elseif (is_array($value)) {
                if (isset($value['tmp_name'])) {
                    $this->checkFileBeforeUpload($value['tmp_name']);
                    if (!isset($value['name'])) {
                        $value['name'] = basename($value);
                    }
                    if (!isset($value['size'])) {
                        $value['size'] = filesize($value);
                    }
                    if (!isset($value['type'])) {
                        $value['type'] = $this->getFileType($value);
                    }
                    if (!isset($value['error'])) {
                        $value['error'] = 0;
                    }
                } else {
                    $files[$name] = $this->formatFilesArray($value);
                }
            } elseif (is_object($value)) {
                
            } else {
                throw new ModuleException(__CLASS__, "Invalid value of key $name in files array");
            }
        }

        return $files;
    }

    private function getFileType($file)
    {
        if (function_exists('mime_content_type') && mime_content_type($file)) {
            return mime_content_type($file);
        }
        return 'application/octet-stream';
    }

    private function checkFileBeforeUpload($file)
    {
        if (!file_exists($file)) {
            throw new ModuleException(__CLASS__, "File $file does not exist");
        }
        if (!is_readable($file)) {
            throw new ModuleException(__CLASS__, "File $file is not readable");
        }
        if (!is_file($file)) {
            throw new ModuleException(__CLASS__, "File $file is not a regular file");
        }
    }

    
    public function seeResponseIsJson()
    {
        $responseContent = $this->connectionModule->_getResponseContent();
        \PHPUnit_Framework_Assert::assertNotEquals('', $responseContent, 'response is empty');
        json_decode($responseContent);
        $errorCode = json_last_error();
        $errorMessage = json_last_error_msg();
        \PHPUnit_Framework_Assert::assertEquals(
            JSON_ERROR_NONE,
            $errorCode,
            sprintf(
                "Invalid json: %s. System message: %s.",
                $responseContent,
                $errorMessage
            )
        );
    }

    
    public function seeResponseContains($text)
    {
        $this->assertContains($text, $this->connectionModule->_getResponseContent(), "REST response contains");
    }

    
    public function dontSeeResponseContains($text)
    {
        $this->assertNotContains($text, $this->connectionModule->_getResponseContent(), "REST response contains");
    }

    
    public function seeResponseContainsJson($json = [])
    {
        \PHPUnit_Framework_Assert::assertThat(
            $this->connectionModule->_getResponseContent(),
            new JsonContains($json)
        );
    }

    
    public function grabResponse()
    {
        return $this->connectionModule->_getResponseContent();
    }

    
    public function grabDataFromResponseByJsonPath($jsonPath)
    {
        return (new JsonArray($this->connectionModule->_getResponseContent()))->filterByJsonPath($jsonPath);
    }

    
    public function seeResponseJsonMatchesXpath($xpath)
    {
        $response = $this->connectionModule->_getResponseContent();
        $this->assertGreaterThan(
            0,
            (new JsonArray($response))->filterByXPath($xpath)->length,
            "Received JSON did not match the XPath `$xpath`.\nJson Response: \n" . $response
        );
    }

    
    public function dontSeeResponseJsonMatchesXpath($xpath)
    {
        $response = $this->connectionModule->_getResponseContent();
        $this->assertEquals(
            0,
            (new JsonArray($response))->filterByXPath($xpath)->length,
            "Received JSON matched the XPath `$xpath`.\nJson Response: \n" . $response
        );
    }

    
    public function seeResponseJsonMatchesJsonPath($jsonPath)
    {
        $response = $this->connectionModule->_getResponseContent();
        $this->assertNotEmpty(
            (new JsonArray($response))->filterByJsonPath($jsonPath),
            "Received JSON did not match the JsonPath `$jsonPath`.\nJson Response: \n" . $response
        );
    }

    
    public function dontSeeResponseJsonMatchesJsonPath($jsonPath)
    {
        $response = $this->connectionModule->_getResponseContent();
        $this->assertEmpty(
            (new JsonArray($response))->filterByJsonPath($jsonPath),
            "Received JSON matched the JsonPath `$jsonPath`.\nJson Response: \n" . $response
        );
    }

    
    public function dontSeeResponseContainsJson($json = [])
    {
        $jsonResponseArray = new JsonArray($this->connectionModule->_getResponseContent());
        $this->assertFalse(
            $jsonResponseArray->containsArray($json),
            "Response JSON contains provided JSON\n"
            . "- <info>" . var_export($json, true) . "</info>\n"
            . "+ " . var_export($jsonResponseArray->toArray(), true)
        );
    }

    
    public function seeResponseMatchesJsonType(array $jsonType, $jsonPath = null)
    {
        $jsonArray = new JsonArray($this->connectionModule->_getResponseContent());
        if ($jsonPath) {
            $jsonArray = $jsonArray->filterByJsonPath($jsonPath);
        }

        \PHPUnit_Framework_Assert::assertThat($jsonArray, new JsonTypeConstraint($jsonType));
    }

    
    public function dontSeeResponseMatchesJsonType($jsonType, $jsonPath = null)
    {
        $jsonArray = new JsonArray($this->connectionModule->_getResponseContent());
        if ($jsonPath) {
            $jsonArray = $jsonArray->filterByJsonPath($jsonPath);
        }

        \PHPUnit_Framework_Assert::assertThat($jsonArray, new JsonTypeConstraint($jsonType, false));
    }

    
    public function seeResponseEquals($expected)
    {
        $this->assertEquals($expected, $this->connectionModule->_getResponseContent());
    }

    
    public function seeResponseCodeIs($code)
    {
        $this->connectionModule->seeResponseCodeIs($code);
    }

    
    public function dontSeeResponseCodeIs($code)
    {
        $this->connectionModule->dontSeeResponseCodeIs($code);
    }

    
    public function seeResponseIsXml()
    {
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($this->connectionModule->_getResponseContent());
        $num = "";
        $title = "";
        if ($doc === false) {
            $error = libxml_get_last_error();
            $num = $error->code;
            $title = trim($error->message);
            libxml_clear_errors();
        }
        libxml_use_internal_errors(false);
        \PHPUnit_Framework_Assert::assertNotSame(
            false,
            $doc,
            "xml decoding error #$num with message \"$title\", see http://www.xmlsoft.org/html/libxml-xmlerror.html"
        );
    }

    
    public function seeXmlResponseMatchesXpath($xpath)
    {
        $structure = new XmlStructure($this->connectionModule->_getResponseContent());
        $this->assertTrue($structure->matchesXpath($xpath), 'xpath not matched');
    }

    
    public function dontSeeXmlResponseMatchesXpath($xpath)
    {
        $structure = new XmlStructure($this->connectionModule->_getResponseContent());
        $this->assertFalse($structure->matchesXpath($xpath), 'accidentally matched xpath');
    }

    
    public function grabTextContentFromXmlElement($cssOrXPath)
    {
        $el = (new XmlStructure($this->connectionModule->_getResponseContent()))->matchElement($cssOrXPath);
        return $el->textContent;
    }

    
    public function grabAttributeFromXmlElement($cssOrXPath, $attribute)
    {
        $el = (new XmlStructure($this->connectionModule->_getResponseContent()))->matchElement($cssOrXPath);
        if (!$el->hasAttribute($attribute)) {
            $this->fail("Attribute not found in element matched by '$cssOrXPath'");
        }
        return $el->getAttribute($attribute);
    }

    
    public function seeXmlResponseEquals($xml)
    {
        \PHPUnit_Framework_Assert::assertXmlStringEqualsXmlString($this->connectionModule->_getResponseContent(), $xml);
    }


    
    public function dontSeeXmlResponseEquals($xml)
    {
        \PHPUnit_Framework_Assert::assertXmlStringNotEqualsXmlString(
            $this->connectionModule->_getResponseContent(),
            $xml
        );
    }

    
    public function seeXmlResponseIncludes($xml)
    {
        $this->assertContains(
            XmlUtils::toXml($xml)->C14N(),
            XmlUtils::toXml($this->connectionModule->_getResponseContent())->C14N(),
            "found in XML Response"
        );
    }

    
    public function dontSeeXmlResponseIncludes($xml)
    {
        $this->assertNotContains(
            XmlUtils::toXml($xml)->C14N(),
            XmlUtils::toXml($this->connectionModule->_getResponseContent())->C14N(),
            "found in XML Response"
        );
    }

    
    public function grabDataFromJsonResponse($path)
    {
        throw new ModuleException(
            $this,
            "This action was deprecated in Codeception 2.0.9 and removed in 2.1. "
            . "Please use `grabDataFromResponseByJsonPath` instead"
        );
    }

    
    public function stopFollowingRedirects()
    {
        $this->client->followRedirects(false);
    }

    
    public function startFollowingRedirects()
    {
        $this->client->followRedirects(true);
    }
}
