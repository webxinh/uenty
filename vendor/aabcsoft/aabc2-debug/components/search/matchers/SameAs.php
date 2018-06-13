<?php


namespace aabc\debug\components\search\matchers;

use aabc\helpers\VarDumper;


class SameAs extends Base
{
    
    public $partial = false;


    
    public function match($value)
    {
        if (!is_scalar($value)) {
            $value = VarDumper::export($value);
        }
        if ($this->partial) {
            return mb_stripos($value, $this->baseValue, 0, \Aabc::$app->charset) !== false;
        } else {
            return strcmp(mb_strtoupper($this->baseValue, \Aabc::$app->charset), mb_strtoupper($value, \Aabc::$app->charset)) === 0;
        }
    }
}
