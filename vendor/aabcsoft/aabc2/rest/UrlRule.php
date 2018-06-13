<?php


namespace aabc\rest;

use Aabc;
use aabc\base\InvalidConfigException;
use aabc\helpers\Inflector;
use aabc\web\CompositeUrlRule;


class UrlRule extends CompositeUrlRule
{
    
    public $prefix;
    
    public $suffix;
    
    public $controller;
    
    public $only = [];
    
    public $except = [];
    
    public $extraPatterns = [];
    
    public $tokens = [
        '{id}' => '<id:\\d[\\d,]*>',
    ];
    
    public $patterns = [
        'PUT,PATCH {id}' => 'update',
        'DELETE {id}' => 'delete',
        'GET,HEAD {id}' => 'view',
        'POST' => 'create',
        'GET,HEAD' => 'index',
        '{id}' => 'options',
        '' => 'options',
    ];
    
    public $ruleConfig = [
        'class' => 'aabc\web\UrlRule',
    ];
    
    public $pluralize = true;


    
    public function init()
    {
        if (empty($this->controller)) {
            throw new InvalidConfigException('"controller" must be set.');
        }

        $controllers = [];
        foreach ((array) $this->controller as $urlName => $controller) {
            if (is_int($urlName)) {
                $urlName = $this->pluralize ? Inflector::pluralize($controller) : $controller;
            }
            $controllers[$urlName] = $controller;
        }
        $this->controller = $controllers;

        $this->prefix = trim($this->prefix, '/');

        parent::init();
    }

    
    protected function createRules()
    {
        $only = array_flip($this->only);
        $except = array_flip($this->except);
        $patterns = $this->extraPatterns + $this->patterns;
        $rules = [];
        foreach ($this->controller as $urlName => $controller) {
            $prefix = trim($this->prefix . '/' . $urlName, '/');
            foreach ($patterns as $pattern => $action) {
                if (!isset($except[$action]) && (empty($only) || isset($only[$action]))) {
                    $rules[$urlName][] = $this->createRule($pattern, $prefix, $controller . '/' . $action);
                }
            }
        }

        return $rules;
    }

    
    protected function createRule($pattern, $prefix, $action)
    {
        $verbs = 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS';
        if (preg_match("/^((?:($verbs),)*($verbs))(?:\\s+(.*))?$/", $pattern, $matches)) {
            $verbs = explode(',', $matches[1]);
            $pattern = isset($matches[4]) ? $matches[4] : '';
        } else {
            $verbs = [];
        }

        $config = $this->ruleConfig;
        $config['verb'] = $verbs;
        $config['pattern'] = rtrim($prefix . '/' . strtr($pattern, $this->tokens), '/');
        $config['route'] = $action;
        if (!empty($verbs) && !in_array('GET', $verbs)) {
            $config['mode'] = \aabc\web\UrlRule::PARSING_ONLY;
        }
        $config['suffix'] = $this->suffix;

        return Aabc::createObject($config);
    }

    
    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        foreach ($this->rules as $urlName => $rules) {
            if (strpos($pathInfo, $urlName) !== false) {
                foreach ($rules as $rule) {
                    /* @var $rule \aabc\web\UrlRule */
                    $result = $rule->parseRequest($manager, $request);
                    if (AABC_DEBUG) {
                        Aabc::trace([
                            'rule' => method_exists($rule, '__toString') ? $rule->__toString() : get_class($rule),
                            'match' => $result !== false,
                            'parent' => self::className()
                        ], __METHOD__);
                    }
                    if ($result !== false) {
                        return $result;
                    }
                }
            }
        }

        return false;
    }

    
    public function createUrl($manager, $route, $params)
    {
        foreach ($this->controller as $urlName => $controller) {
            if (strpos($route, $controller) !== false) {
                foreach ($this->rules[$urlName] as $rule) {
                    /* @var $rule \aabc\web\UrlRule */
                    if (($url = $rule->createUrl($manager, $route, $params)) !== false) {
                        return $url;
                    }
                }
            }
        }

        return false;
    }
}
