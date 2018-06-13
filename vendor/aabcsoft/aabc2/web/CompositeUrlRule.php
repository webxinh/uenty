<?php


namespace aabc\web;

use Aabc;
use aabc\base\Object;


abstract class CompositeUrlRule extends Object implements UrlRuleInterface
{
    
    protected $rules = [];


    
    abstract protected function createRules();

    
    public function init()
    {
        parent::init();
        $this->rules = $this->createRules();
    }

    
    public function parseRequest($manager, $request)
    {
        foreach ($this->rules as $rule) {
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

        return false;
    }

    
    public function createUrl($manager, $route, $params)
    {
        foreach ($this->rules as $rule) {
            /* @var $rule \aabc\web\UrlRule */
            if (($url = $rule->createUrl($manager, $route, $params)) !== false) {
                return $url;
            }
        }

        return false;
    }
}
