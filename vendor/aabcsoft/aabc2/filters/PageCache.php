<?php


namespace aabc\filters;

use Aabc;
use aabc\base\Action;
use aabc\base\ActionFilter;
use aabc\caching\Cache;
use aabc\caching\Dependency;
use aabc\di\Instance;
use aabc\web\Response;


class PageCache extends ActionFilter
{
    
    public $varyByRoute = true;
    
    public $cache = 'cache';
    
    public $duration = 60;
    
    public $dependency;
    
    public $variations;
    
    public $enabled = true;
    
    public $view;
    
    public $cacheCookies = false;
    
    public $cacheHeaders = true;
    
    public $dynamicPlaceholders;


    
    public function init()
    {
        parent::init();
        if ($this->view === null) {
            $this->view = Aabc::$app->getView();
        }
    }

    //Hàm xóa cache
    public function clear($key='')
    {
        $a = new PageCache();
        $a->beforeAction(null,$key);        
    }
    
    public function beforeAction($action,$key = '')
    {
        if (!$this->enabled) {
            return true;
        }

        $this->cache = Instance::ensure($this->cache, Cache::className());

        if (is_array($this->dependency)) {
            $this->dependency = Aabc::createObject($this->dependency);
        }
        $response = Aabc::$app->getResponse();

        //Tùy chọn xóa Cache theo key mong muốn (cache Trang)
        if(!empty($key)){
            if($key == 'homepage') $key = '';
            $arr = [
                '0' => 'aabc\filters\PageCache',
                '1' => $key,
                '2' => 'en-US',
            ] ;              
            $this->cache->delete($arr);
            return true;
        }        

        $data = $this->cache->get($this->calculateCacheKey());
        if (!is_array($data) || !isset($data['cacheVersion']) || $data['cacheVersion'] !== 1) {
            $this->view->cacheStack[] = $this;
            ob_start();
            ob_implicit_flush(false);
            $response->on(Response::EVENT_AFTER_SEND, [$this, 'cacheResponse']);
            Aabc::trace('Valid page content is not found in the cache.', __METHOD__);
            return true;
        } else {
            $this->restoreResponse($response, $data);
            Aabc::trace('Valid page content is found in the cache.', __METHOD__);
            return false;
        }
    }

    
    public function beforeCacheResponse()
    {
        return true;
    }

    
    public function afterRestoreResponse($data)
    {
    }

    
    protected function restoreResponse($response, $data)
    {
        foreach (['format', 'version', 'statusCode', 'statusText', 'content'] as $name) {
            $response->{$name} = $data[$name];
        }
        foreach (['headers', 'cookies'] as $name) {
            if (isset($data[$name]) && is_array($data[$name])) {
                $response->{$name}->fromArray(array_merge($data[$name], $response->{$name}->toArray()));
            }
        }
        if (!empty($data['dynamicPlaceholders']) && is_array($data['dynamicPlaceholders'])) {
            if (empty($this->view->cacheStack)) {
                // outermost cache: replace placeholder with dynamic content
                $response->content = $this->updateDynamicContent($response->content, $data['dynamicPlaceholders']);
            }
            foreach ($data['dynamicPlaceholders'] as $name => $statements) {
                $this->view->addDynamicPlaceholder($name, $statements);
            }
        }
        $this->afterRestoreResponse(isset($data['cacheData']) ? $data['cacheData'] : null);
    }

    
    public function cacheResponse()
    {
        array_pop($this->view->cacheStack);
        $beforeCacheResponseResult = $this->beforeCacheResponse();
        if ($beforeCacheResponseResult === false) {
            $content = ob_get_clean();
            if (empty($this->view->cacheStack) && !empty($this->dynamicPlaceholders)) {
                $content = $this->updateDynamicContent($content, $this->dynamicPlaceholders);
            }
            echo $content;
            return;
        }

        $response = Aabc::$app->getResponse();
        $data = [
            'cacheVersion' => 1,
            'cacheData' => is_array($beforeCacheResponseResult) ? $beforeCacheResponseResult : null,
            'content' => ob_get_clean()
        ];
        if ($data['content'] === false || $data['content'] === '') {
            return;
        }

        $data['dynamicPlaceholders'] = $this->dynamicPlaceholders;
        foreach (['format', 'version', 'statusCode', 'statusText'] as $name) {
            $data[$name] = $response->{$name};
        }
        $this->insertResponseCollectionIntoData($response, 'headers', $data);
        $this->insertResponseCollectionIntoData($response, 'cookies', $data);
        $this->cache->set($this->calculateCacheKey(), $data, $this->duration, $this->dependency);
        if (empty($this->view->cacheStack) && !empty($this->dynamicPlaceholders)) {
            $data['content'] = $this->updateDynamicContent($data['content'], $this->dynamicPlaceholders);
        }
        echo $data['content'];
    }

    
    private function insertResponseCollectionIntoData(Response $response, $collectionName, array &$data)
    {
        $property = 'cache' . ucfirst($collectionName);
        if ($this->{$property} === false) {
            return;
        }

        $all = $response->{$collectionName}->toArray();
        if (is_array($this->{$property})) {
            $filtered = [];
            foreach ($this->{$property} as $name) {
                if ($collectionName === 'headers') {
                    $name = strtolower($name);
                }
                if (isset($all[$name])) {
                    $filtered[$name] = $all[$name];
                }
            }
            $all = $filtered;
        }
        $data[$collectionName] = $all;
    }

    
    protected function updateDynamicContent($content, $placeholders)
    {
        foreach ($placeholders as $name => $statements) {
            $placeholders[$name] = $this->view->evaluateDynamicContent($statements);
        }

        return strtr($content, $placeholders);
    }

    
    protected function calculateCacheKey()
    {
        $key = [__CLASS__];
        if ($this->varyByRoute) {
            $key[] = Aabc::$app->requestedRoute;
        }
        if (is_array($this->variations)) {
            foreach ($this->variations as $value) {
                $key[] = $value;
            }
        }
        return $key;
    }
}
