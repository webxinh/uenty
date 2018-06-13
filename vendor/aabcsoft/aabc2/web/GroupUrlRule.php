<?php


namespace aabc\web;

use Aabc;
use aabc\base\InvalidConfigException;


class GroupUrlRule extends CompositeUrlRule
{
    
    public $rules = [];
    
    public $prefix;
    
    public $routePrefix;
    
    public $ruleConfig = ['class' => 'aabc\web\UrlRule'];


    
    public function init()
    {
        $this->prefix = trim($this->prefix, '/');
        $this->routePrefix = $this->routePrefix === null ? $this->prefix : trim($this->routePrefix, '/');
        parent::init();
    }

    
    protected function createRules()
    {
        $rules = [];
        foreach ($this->rules as $key => $rule) {
            if (!is_array($rule)) {
                $rule = [
                    'pattern' => ltrim($this->prefix . '/' . $key, '/'),
                    'route' => ltrim($this->routePrefix . '/' . $rule, '/'),
                ];
            } elseif (isset($rule['pattern'], $rule['route'])) {
                $rule['pattern'] = ltrim($this->prefix . '/' . $rule['pattern'], '/');
                $rule['route'] = ltrim($this->routePrefix . '/' . $rule['route'], '/');
            }

            $rule = Aabc::createObject(array_merge($this->ruleConfig, $rule));
            if (!$rule instanceof UrlRuleInterface) {
                throw new InvalidConfigException('URL rule class must implement UrlRuleInterface.');
            }
            $rules[] = $rule;
        }
        return $rules;
    }

    
    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        if ($this->prefix === '' || strpos($pathInfo . '/', $this->prefix . '/') === 0) {
            return parent::parseRequest($manager, $request);
        } else {
            return false;
        }
    }

    
    public function createUrl($manager, $route, $params)
    {
        if ($this->routePrefix === '' || strpos($route, $this->routePrefix . '/') === 0) {
            return parent::createUrl($manager, $route, $params);
        } else {
            return false;
        }
    }
}
