<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\BrowserKit;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Link;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\Process\PhpProcess;


abstract class Client
{
    protected $history;
    protected $cookieJar;
    protected $server = array();
    protected $internalRequest;
    protected $request;
    protected $internalResponse;
    protected $response;
    protected $crawler;
    protected $insulated = false;
    protected $redirect;
    protected $followRedirects = true;

    private $maxRedirects = -1;
    private $redirectCount = 0;
    private $isMainRequest = true;

    
    public function __construct(array $server = array(), History $history = null, CookieJar $cookieJar = null)
    {
        $this->setServerParameters($server);
        $this->history = $history ?: new History();
        $this->cookieJar = $cookieJar ?: new CookieJar();
    }

    
    public function followRedirects($followRedirect = true)
    {
        $this->followRedirects = (bool) $followRedirect;
    }

    
    public function isFollowingRedirects()
    {
        return $this->followRedirects;
    }

    
    public function setMaxRedirects($maxRedirects)
    {
        $this->maxRedirects = $maxRedirects < 0 ? -1 : $maxRedirects;
        $this->followRedirects = -1 != $this->maxRedirects;
    }

    
    public function getMaxRedirects()
    {
        return $this->maxRedirects;
    }

    
    public function insulate($insulated = true)
    {
        if ($insulated && !class_exists('Symfony\\Component\\Process\\Process')) {
            throw new \RuntimeException('Unable to isolate requests as the Symfony Process Component is not installed.');
        }

        $this->insulated = (bool) $insulated;
    }

    
    public function setServerParameters(array $server)
    {
        $this->server = array_merge(array(
            'HTTP_USER_AGENT' => 'Symfony BrowserKit',
        ), $server);
    }

    
    public function setServerParameter($key, $value)
    {
        $this->server[$key] = $value;
    }

    
    public function getServerParameter($key, $default = '')
    {
        return isset($this->server[$key]) ? $this->server[$key] : $default;
    }

    
    public function getHistory()
    {
        return $this->history;
    }

    
    public function getCookieJar()
    {
        return $this->cookieJar;
    }

    
    public function getCrawler()
    {
        return $this->crawler;
    }

    
    public function getInternalResponse()
    {
        return $this->internalResponse;
    }

    
    public function getResponse()
    {
        return $this->response;
    }

    
    public function getInternalRequest()
    {
        return $this->internalRequest;
    }

    
    public function getRequest()
    {
        return $this->request;
    }

    
    public function click(Link $link)
    {
        if ($link instanceof Form) {
            return $this->submit($link);
        }

        return $this->request($link->getMethod(), $link->getUri());
    }

    
    public function submit(Form $form, array $values = array())
    {
        $form->setValues($values);

        return $this->request($form->getMethod(), $form->getUri(), $form->getPhpValues(), $form->getPhpFiles());
    }

    
    public function request($method, $uri, array $parameters = array(), array $files = array(), array $server = array(), $content = null, $changeHistory = true)
    {
        if ($this->isMainRequest) {
            $this->redirectCount = 0;
        } else {
            ++$this->redirectCount;
        }

        $uri = $this->getAbsoluteUri($uri);

        $server = array_merge($this->server, $server);

        if (isset($server['HTTPS'])) {
            $uri = preg_replace('{^'.parse_url($uri, PHP_URL_SCHEME).'}', $server['HTTPS'] ? 'https' : 'http', $uri);
        }

        if (!$this->history->isEmpty()) {
            $server['HTTP_REFERER'] = $this->history->current()->getUri();
        }

        if (empty($server['HTTP_HOST'])) {
            $server['HTTP_HOST'] = $this->extractHost($uri);
        }

        $server['HTTPS'] = 'https' == parse_url($uri, PHP_URL_SCHEME);

        $this->internalRequest = new Request($uri, $method, $parameters, $files, $this->cookieJar->allValues($uri), $server, $content);

        $this->request = $this->filterRequest($this->internalRequest);

        if (true === $changeHistory) {
            $this->history->add($this->internalRequest);
        }

        if ($this->insulated) {
            $this->response = $this->doRequestInProcess($this->request);
        } else {
            $this->response = $this->doRequest($this->request);
        }

        $this->internalResponse = $this->filterResponse($this->response);

        $this->cookieJar->updateFromResponse($this->internalResponse, $uri);

        $status = $this->internalResponse->getStatus();

        if ($status >= 300 && $status < 400) {
            $this->redirect = $this->internalResponse->getHeader('Location');
        } else {
            $this->redirect = null;
        }

        if ($this->followRedirects && $this->redirect) {
            return $this->crawler = $this->followRedirect();
        }

        return $this->crawler = $this->createCrawlerFromContent($this->internalRequest->getUri(), $this->internalResponse->getContent(), $this->internalResponse->getHeader('Content-Type'));
    }

    
    protected function doRequestInProcess($request)
    {
        $process = new PhpProcess($this->getScript($request), null, null);
        $process->run();

        if (!$process->isSuccessful() || !preg_match('/^O\:\d+\:/', $process->getOutput())) {
            throw new \RuntimeException(sprintf('OUTPUT: %s ERROR OUTPUT: %s', $process->getOutput(), $process->getErrorOutput()));
        }

        return unserialize($process->getOutput());
    }

    
    abstract protected function doRequest($request);

    
    protected function getScript($request)
    {
        throw new \LogicException('To insulate requests, you need to override the getScript() method.');
    }

    
    protected function filterRequest(Request $request)
    {
        return $request;
    }

    
    protected function filterResponse($response)
    {
        return $response;
    }

    
    protected function createCrawlerFromContent($uri, $content, $type)
    {
        if (!class_exists('Symfony\Component\DomCrawler\Crawler')) {
            return;
        }

        $crawler = new Crawler(null, $uri);
        $crawler->addContent($content, $type);

        return $crawler;
    }

    
    public function back()
    {
        return $this->requestFromRequest($this->history->back(), false);
    }

    
    public function forward()
    {
        return $this->requestFromRequest($this->history->forward(), false);
    }

    
    public function reload()
    {
        return $this->requestFromRequest($this->history->current(), false);
    }

    
    public function followRedirect()
    {
        if (empty($this->redirect)) {
            throw new \LogicException('The request was not redirected.');
        }

        if (-1 !== $this->maxRedirects) {
            if ($this->redirectCount > $this->maxRedirects) {
                throw new \LogicException(sprintf('The maximum number (%d) of redirections was reached.', $this->maxRedirects));
            }
        }

        $request = $this->internalRequest;

        if (in_array($this->internalResponse->getStatus(), array(302, 303))) {
            $method = 'GET';
            $files = array();
            $content = null;
        } else {
            $method = $request->getMethod();
            $files = $request->getFiles();
            $content = $request->getContent();
        }

        if ('GET' === strtoupper($method)) {
            // Don't forward parameters for GET request as it should reach the redirection URI
            $parameters = array();
        } else {
            $parameters = $request->getParameters();
        }

        $server = $request->getServer();
        $server = $this->updateServerFromUri($server, $this->redirect);

        $this->isMainRequest = false;

        $response = $this->request($method, $this->redirect, $parameters, $files, $server, $content);

        $this->isMainRequest = true;

        return $response;
    }

    
    public function restart()
    {
        $this->cookieJar->clear();
        $this->history->clear();
    }

    
    protected function getAbsoluteUri($uri)
    {
        // already absolute?
        if (0 === strpos($uri, 'http://') || 0 === strpos($uri, 'https://')) {
            return $uri;
        }

        if (!$this->history->isEmpty()) {
            $currentUri = $this->history->current()->getUri();
        } else {
            $currentUri = sprintf('http%s://%s/',
                isset($this->server['HTTPS']) ? 's' : '',
                isset($this->server['HTTP_HOST']) ? $this->server['HTTP_HOST'] : 'localhost'
            );
        }

        // protocol relative URL
        if (0 === strpos($uri, '//')) {
            return parse_url($currentUri, PHP_URL_SCHEME).':'.$uri;
        }

        // anchor or query string parameters?
        if (!$uri || '#' == $uri[0] || '?' == $uri[0]) {
            return preg_replace('/[#?].*?$/', '', $currentUri).$uri;
        }

        if ('/' !== $uri[0]) {
            $path = parse_url($currentUri, PHP_URL_PATH);

            if ('/' !== substr($path, -1)) {
                $path = substr($path, 0, strrpos($path, '/') + 1);
            }

            $uri = $path.$uri;
        }

        return preg_replace('#^(.*?//[^/]+)\/.*$#', '$1', $currentUri).$uri;
    }

    
    protected function requestFromRequest(Request $request, $changeHistory = true)
    {
        return $this->request($request->getMethod(), $request->getUri(), $request->getParameters(), $request->getFiles(), $request->getServer(), $request->getContent(), $changeHistory);
    }

    private function updateServerFromUri($server, $uri)
    {
        $server['HTTP_HOST'] = $this->extractHost($uri);
        $scheme = parse_url($uri, PHP_URL_SCHEME);
        $server['HTTPS'] = null === $scheme ? $server['HTTPS'] : 'https' == $scheme;
        unset($server['HTTP_IF_NONE_MATCH'], $server['HTTP_IF_MODIFIED_SINCE']);

        return $server;
    }

    private function extractHost($uri)
    {
        $host = parse_url($uri, PHP_URL_HOST);

        if ($port = parse_url($uri, PHP_URL_PORT)) {
            return $host.':'.$port;
        }

        return $host;
    }
}
