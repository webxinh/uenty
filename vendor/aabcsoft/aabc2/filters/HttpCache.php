<?php


namespace aabc\filters;

use Aabc;
use aabc\base\ActionFilter;
use aabc\base\Action;


class HttpCache extends ActionFilter
{
    
    public $lastModified;
    
    public $etagSeed;
    
    public $weakEtag = false;
    
    public $params;
    
    public $cacheControlHeader = 'public, max-age=3600';
    
    public $sessionCacheLimiter = '';
    
    public $enabled = true;


    
    public function beforeAction($action)
    {
        if (!$this->enabled) {
            return true;
        }

        $verb = Aabc::$app->getRequest()->getMethod();
        if ($verb !== 'GET' && $verb !== 'HEAD' || $this->lastModified === null && $this->etagSeed === null) {
            return true;
        }

        $lastModified = $etag = null;
        if ($this->lastModified !== null) {
            $lastModified = call_user_func($this->lastModified, $action, $this->params);
        }
        if ($this->etagSeed !== null) {
            $seed = call_user_func($this->etagSeed, $action, $this->params);
            if ($seed !== null) {
                $etag = $this->generateEtag($seed);
            }
        }

        $this->sendCacheControlHeader();

        $response = Aabc::$app->getResponse();
        if ($etag !== null) {
            $response->getHeaders()->set('Etag', $etag);
        }

        $cacheValid = $this->validateCache($lastModified, $etag);
        // https://tools.ietf.org/html/rfc7232#section-4.1
        if ($lastModified !== null && (!$cacheValid || ($cacheValid && $etag === null))) {
            $response->getHeaders()->set('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
        }
        if ($cacheValid) {
            $response->setStatusCode(304);
            return false;
        }

        return true;
    }

    
    protected function validateCache($lastModified, $etag)
    {
        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
            // HTTP_IF_NONE_MATCH takes precedence over HTTP_IF_MODIFIED_SINCE
            // http://tools.ietf.org/html/rfc7232#section-3.3
            return $etag !== null && in_array($etag, Aabc::$app->request->getETags(), true);
        } elseif (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            return $lastModified !== null && @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModified;
        } else {
            return false;
        }
    }

    
    protected function sendCacheControlHeader()
    {
        if ($this->sessionCacheLimiter !== null) {
            if ($this->sessionCacheLimiter === '' && !headers_sent() && Aabc::$app->getSession()->getIsActive()) {
                header_remove('Expires');
                header_remove('Cache-Control');
                header_remove('Last-Modified');
                header_remove('Pragma');
            }
            session_cache_limiter($this->sessionCacheLimiter);
        }

        $headers = Aabc::$app->getResponse()->getHeaders();

        if ($this->cacheControlHeader !== null) {
            $headers->set('Cache-Control', $this->cacheControlHeader);
        }
    }

    
    protected function generateEtag($seed)
    {
        $etag =  '"' . rtrim(base64_encode(sha1($seed, true)), '=') . '"';
        return $this->weakEtag ? 'W/' . $etag : $etag;
    }
}
