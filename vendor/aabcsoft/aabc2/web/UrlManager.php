<?php


namespace aabc\web;

use Aabc;
use aabc\base\Component;
use aabc\base\InvalidConfigException;
use aabc\caching\Cache;
use aabc\helpers\Url;


class UrlManager extends Component
{
    
    public $enablePrettyUrl = false;
    
    public $enableStrictParsing = false;
    
    public $rules = [];
    
    public $suffix;
    
    public $showScriptName = true;
    
    public $routeParam = 'r';
    
    public $cache = 'cache';
    
    public $ruleConfig = ['class' => 'aabc\web\UrlRule'];
    
    public $normalizer = false;

    
    protected $cacheKey = __CLASS__;

    private $_baseUrl;
    private $_scriptUrl;
    private $_hostInfo;
    private $_ruleCache;


    
    public function init()
    {
        parent::init();

        if ($this->normalizer !== false) {
            $this->normalizer = Aabc::createObject($this->normalizer);
            if (!$this->normalizer instanceof UrlNormalizer) {
                throw new InvalidConfigException('`' . get_class($this) . '::normalizer` should be an instance of `' . UrlNormalizer::className() . '` or its DI compatible configuration.');
            }
        }

        if (!$this->enablePrettyUrl || empty($this->rules)) {
            return;
        }
        if (is_string($this->cache)) {
            $this->cache = Aabc::$app->get($this->cache, false);
        }
        if ($this->cache instanceof Cache) {
            $cacheKey = $this->cacheKey;
            $hash = md5(json_encode($this->rules));
            if (($data = $this->cache->get($cacheKey)) !== false && isset($data[1]) && $data[1] === $hash) {
                $this->rules = $data[0];
            } else {
                $this->rules = $this->buildRules($this->rules);
                $this->cache->set($cacheKey, [$this->rules, $hash]);
            }
        } else {
            $this->rules = $this->buildRules($this->rules);
        }
    }

    
    public function addRules($rules, $append = true)
    {
        if (!$this->enablePrettyUrl) {
            return;
        }
        $rules = $this->buildRules($rules);
        if ($append) {
            $this->rules = array_merge($this->rules, $rules);
        } else {
            $this->rules = array_merge($rules, $this->rules);
        }
    }

    
    protected function buildRules($rules)
    {
        $compiledRules = [];
        $verbs = 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS';
        foreach ($rules as $key => $rule) {
            if (is_string($rule)) {
                $rule = ['route' => $rule];
                if (preg_match("/^((?:($verbs),)*($verbs))\\s+(.*)$/", $key, $matches)) {
                    $rule['verb'] = explode(',', $matches[1]);
                    // rules that do not apply for GET requests should not be use to create urls
                    if (!in_array('GET', $rule['verb'])) {
                        $rule['mode'] = UrlRule::PARSING_ONLY;
                    }
                    $key = $matches[4];
                }
                $rule['pattern'] = $key;
            }
            if (is_array($rule)) {
                $rule = Aabc::createObject(array_merge($this->ruleConfig, $rule));
            }
            if (!$rule instanceof UrlRuleInterface) {
                throw new InvalidConfigException('URL rule class must implement UrlRuleInterface.');
            }
            $compiledRules[] = $rule;
        }
        return $compiledRules;
    }

    
    public function parseRequest($request)
    {
        if ($this->enablePrettyUrl) {
            /* @var $rule UrlRule */
            foreach ($this->rules as $rule) {
                $result = $rule->parseRequest($this, $request);
                if (AABC_DEBUG) {
                    Aabc::trace([
                        'rule' => method_exists($rule, '__toString') ? $rule->__toString() : get_class($rule),
                        'match' => $result !== false,
                        'parent' => null
                    ], __METHOD__);
                }
                if ($result !== false) {
                    return $result;
                }
            }

            if ($this->enableStrictParsing) {
                return false;
            }

            Aabc::trace('No matching URL rules. Using default URL parsing logic.', __METHOD__);

            $suffix = (string) $this->suffix;
            $pathInfo = $request->getPathInfo();
            $normalized = false;
            if ($this->normalizer !== false) {
                $pathInfo = $this->normalizer->normalizePathInfo($pathInfo, $suffix, $normalized);
            }
            if ($suffix !== '' && $pathInfo !== '') {
                $n = strlen($this->suffix);
                if (substr_compare($pathInfo, $this->suffix, -$n, $n) === 0) {
                    $pathInfo = substr($pathInfo, 0, -$n);
                    if ($pathInfo === '') {
                        // suffix alone is not allowed
                        return false;
                    }
                } else {
                    // suffix doesn't match
                    return false;
                }
            }

            if ($normalized) {
                // pathInfo was changed by normalizer - we need also normalize route
                return $this->normalizer->normalizeRoute([$pathInfo, []]);
            } else {
                return [$pathInfo, []];
            }
        } else {
            Aabc::trace('Pretty URL not enabled. Using default URL parsing logic.', __METHOD__);
            $route = $request->getQueryParam($this->routeParam, '');
            if (is_array($route)) {
                $route = '';
            }

            return [(string) $route, []];
        }
    }

    
    public function createUrl($params)
    {
        $params = (array) $params;
        $anchor = isset($params['#']) ? '#' . $params['#'] : '';
        unset($params['#'], $params[$this->routeParam]);

        $route = trim($params[0], '/');
        unset($params[0]);

        $baseUrl = $this->showScriptName || !$this->enablePrettyUrl ? $this->getScriptUrl() : $this->getBaseUrl();

        if ($this->enablePrettyUrl) {
            $cacheKey = $route . '?';
            foreach ($params as $key => $value) {
                if ($value !== null) {
                    $cacheKey .= $key . '&';
                }
            }

            $url = $this->getUrlFromCache($cacheKey, $route, $params);

            if ($url === false) {
                $cacheable = true;
                foreach ($this->rules as $rule) {
                    /* @var $rule UrlRule */
                    if (!empty($rule->defaults) && $rule->mode !== UrlRule::PARSING_ONLY) {
                        // if there is a rule with default values involved, the matching result may not be cached
                        $cacheable = false;
                    }
                    if (($url = $rule->createUrl($this, $route, $params)) !== false) {
                        if ($cacheable) {
                            $this->setRuleToCache($cacheKey, $rule);
                        }
                        break;
                    }
                }
            }

            if ($url !== false) {
                if (strpos($url, '://') !== false) {
                    if ($baseUrl !== '' && ($pos = strpos($url, '/', 8)) !== false) {
                        return substr($url, 0, $pos) . $baseUrl . substr($url, $pos) . $anchor;
                    } else {
                        return $url . $baseUrl . $anchor;
                    }
                } elseif (strpos($url, '//') === 0) {
                    if ($baseUrl !== '' && ($pos = strpos($url, '/', 2)) !== false) {
                        return substr($url, 0, $pos) . $baseUrl . substr($url, $pos) . $anchor;
                    } else {
                        return $url . $baseUrl . $anchor;
                    }
                } else {
                    return "$baseUrl/{$url}{$anchor}";
                }
            }

            if ($this->suffix !== null) {
                $route .= $this->suffix;
            }
            if (!empty($params) && ($query = http_build_query($params)) !== '') {
                $route .= '?' . $query;
            }

            return "$baseUrl/{$route}{$anchor}";
        } else {
            $url = "$baseUrl?{$this->routeParam}=" . urlencode($route);
            if (!empty($params) && ($query = http_build_query($params)) !== '') {
                $url .= '&' . $query;
            }

            return $url . $anchor;
        }
    }

    
    protected function getUrlFromCache($cacheKey, $route, $params)
    {
        if (!empty($this->_ruleCache[$cacheKey])) {
            foreach ($this->_ruleCache[$cacheKey] as $rule) {
                /* @var $rule UrlRule */
                if (($url = $rule->createUrl($this, $route, $params)) !== false) {
                    return $url;
                }
            }
        } else {
            $this->_ruleCache[$cacheKey] = [];
        }
        return false;
    }

    
    protected function setRuleToCache($cacheKey, UrlRuleInterface $rule)
    {
        $this->_ruleCache[$cacheKey][] = $rule;
    }

    
    public function createAbsoluteUrl($params, $scheme = null)
    {
        $params = (array) $params;
        $url = $this->createUrl($params);
        if (strpos($url, '://') === false) {
            $hostInfo = $this->getHostInfo();
            if (strpos($url, '//') === 0) {
                $url = substr($hostInfo, 0, strpos($hostInfo, '://')) . ':' . $url;
            } else {
                $url = $hostInfo . $url;
            }
        }

        return Url::ensureScheme($url, $scheme);
    }

    
    public function getBaseUrl()
    {
        if ($this->_baseUrl === null) {
            $request = Aabc::$app->getRequest();
            if ($request instanceof Request) {
                $this->_baseUrl = $request->getBaseUrl();
            } else {
                throw new InvalidConfigException('Please configure UrlManager::baseUrl correctly as you are running a console application.');
            }
        }

        return $this->_baseUrl;
    }

    
    public function setBaseUrl($value)
    {
        $this->_baseUrl = $value === null ? null : rtrim($value, '/');
    }

    
    public function getScriptUrl()
    {
        if ($this->_scriptUrl === null) {
            $request = Aabc::$app->getRequest();
            if ($request instanceof Request) {
                $this->_scriptUrl = $request->getScriptUrl();
            } else {
                throw new InvalidConfigException('Please configure UrlManager::scriptUrl correctly as you are running a console application.');
            }
        }

        return $this->_scriptUrl;
    }

    
    public function setScriptUrl($value)
    {
        $this->_scriptUrl = $value;
    }

    
    public function getHostInfo()
    {
        if ($this->_hostInfo === null) {
            $request = Aabc::$app->getRequest();
            if ($request instanceof \aabc\web\Request) {
                $this->_hostInfo = $request->getHostInfo();
            } else {
                throw new InvalidConfigException('Please configure UrlManager::hostInfo correctly as you are running a console application.');
            }
        }

        return $this->_hostInfo;
    }

    
    public function setHostInfo($value)
    {
        $this->_hostInfo = $value === null ? null : rtrim($value, '/');
    }
}
