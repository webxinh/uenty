<?php
namespace Codeception\Lib\Connector;

use Codeception\Util\Uri;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Post\PostFile;
use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Response as BrowserKitResponse;
use GuzzleHttp\Url;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;

class Guzzle extends Client
{
    protected $baseUri;
    protected $requestOptions = [
        'allow_redirects' => false,
        'headers' => [],
    ];
    protected $refreshMaxInterval = 0;


    
    protected $client;

    public function setBaseUri($uri)
    {
        $this->baseUri = $uri;
    }

    
    public function setRefreshMaxInterval($seconds)
    {
        $this->refreshMaxInterval = $seconds;
    }

    public function setClient(\GuzzleHttp\Client $client)
    {
        $this->client = $client;
    }

    
    public function setHeader($name, $value)
    {
        if (strval($value) === '') {
            $this->deleteHeader($name);
        } else {
            $this->requestOptions['headers'][$name] = $value;
        }
    }

    
    public function deleteHeader($name)
    {
        unset($this->requestOptions['headers'][$name]);
    }

    
    public function setAuth($username, $password, $type = 'basic')
    {
        if (!$username) {
            unset($this->requestOptions['auth']);
            return;
        }
        $this->requestOptions['auth'] = [$username, $password, $type];
    }

    
    protected function createResponse(Response $response)
    {
        $contentType = $response->getHeader('Content-Type');

        if (!$contentType) {
            $contentType = 'text/html';
        }

        if (strpos($contentType, 'charset=') === false) {
            $body = $response->getBody(true);
            if (preg_match('/\<meta[^\>]+charset *= *["\']?([a-zA-Z\-0-9]+)/i', $body, $matches)) {
                $contentType .= ';charset=' . $matches[1];
            }
            $response->setHeader('Content-Type', $contentType);
        }

        $headers = $response->getHeaders();
        $status = $response->getStatusCode();
        if ($status < 300 || $status >= 400) {
            $matches = [];

            $matchesMeta = preg_match(
                '/\<meta[^\>]+http-equiv="refresh" content="\s*(\d*)\s*;\s*url=(.*?)"/i',
                $response->getBody(true),
                $matches
            );

            if (!$matchesMeta) {
                // match by header
                preg_match(
                    '/^\s*(\d*)\s*;\s*url=(.*)/i',
                    (string)$response->getHeader('Refresh'),
                    $matches
                );
            }

            if ((!empty($matches)) && (empty($matches[1]) || $matches[1] < $this->refreshMaxInterval)) {
                $uri = $this->getAbsoluteUri($matches[2]);
                $partsUri = parse_url($uri);
                $partsCur = parse_url($this->getHistory()->current()->getUri());
                foreach ($partsCur as $key => $part) {
                    if ($key === 'fragment') {
                        continue;
                    }
                    if (!isset($partsUri[$key]) || $partsUri[$key] !== $part) {
                        $status = 302;
                        $headers['Location'] = $matchesMeta ? htmlspecialchars_decode($uri) : $uri;
                        break;
                    }
                }
            }
        }

        return new BrowserKitResponse($response->getBody(), $status, $headers);
    }

    public function getAbsoluteUri($uri)
    {
        $baseUri = $this->baseUri;
        if (strpos($uri, '://') === false) {
            if (strpos($uri, '/') === 0) {
                $baseUriPath = parse_url($baseUri, PHP_URL_PATH);
                if (!empty($baseUriPath) && strpos($uri, $baseUriPath) === 0) {
                    $uri = substr($uri, strlen($baseUriPath));
                }

                return Uri::appendPath((string)$baseUri, $uri);
            }
            // relative url
            if (!$this->getHistory()->isEmpty()) {
                return Uri::mergeUrls((string)$this->getHistory()->current()->getUri(), $uri);
            }
        }
        return Uri::mergeUrls($baseUri, $uri);
    }

    protected function doRequest($request)
    {
        
        $requestOptions = [
            'body' => $this->extractBody($request),
            'cookies' => $this->extractCookies($request),
            'headers' => $this->extractHeaders($request)
        ];

        $requestOptions = array_replace_recursive($requestOptions, $this->requestOptions);

        $guzzleRequest = $this->client->createRequest(
            $request->getMethod(),
            $request->getUri(),
            $requestOptions
        );
        foreach ($this->extractFiles($request) as $postFile) {
            $guzzleRequest->getBody()->addFile($postFile);
        }

        // Let BrowserKit handle redirects
        try {
            $response = $this->client->send($guzzleRequest);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                throw $e;
            }
        }
        return $this->createResponse($response);
    }

    protected function extractHeaders(BrowserKitRequest $request)
    {
        $headers = [];
        $server = $request->getServer();

        $contentHeaders = ['Content-Length' => true, 'Content-Md5' => true, 'Content-Type' => true];
        foreach ($server as $header => $val) {
            $header = implode('-', array_map('ucfirst', explode('-', strtolower(str_replace('_', '-', $header)))));
            if (strpos($header, 'Http-') === 0) {
                $headers[substr($header, 5)] = $val;
            } elseif (isset($contentHeaders[$header])) {
                $headers[$header] = $val;
            }
        }
        return $headers;
    }

    protected function extractBody(BrowserKitRequest $request)
    {
        if (in_array(strtoupper($request->getMethod()), ['GET', 'HEAD'])) {
            return null;
        }
        if ($request->getContent() !== null) {
            return $request->getContent();
        } else {
            return $request->getParameters();
        }
    }

    protected function extractFiles(BrowserKitRequest $request)
    {
        if (!in_array(strtoupper($request->getMethod()), ['POST', 'PUT'])) {
            return [];
        }

        return $this->mapFiles($request->getFiles());
    }

    protected function mapFiles($requestFiles, $arrayName = '')
    {
        $files = [];
        foreach ($requestFiles as $name => $info) {
            if (!empty($arrayName)) {
                $name = $arrayName.'['.$name.']';
            }

            if (is_array($info)) {
                if (isset($info['tmp_name'])) {
                    if ($info['tmp_name']) {
                        $handle = fopen($info['tmp_name'], 'r');
                        $filename = isset($info['name']) ? $info['name'] : null;

                        $files[] = new PostFile($name, $handle, $filename);
                    }
                } else {
                    $files = array_merge($files, $this->mapFiles($info, $name));
                }
            } else {
                $files[] = new PostFile($name, fopen($info, 'r'));
            }
        }

        return $files;
    }

    protected function extractCookies(BrowserKitRequest $request)
    {
        return $this->getCookieJar()->allRawValues($request->getUri());
    }
}
