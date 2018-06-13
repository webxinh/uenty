<?php


namespace aabc\debug\components\search\matchers;

use aabc\base\Component;


abstract class Base extends Component implements MatcherInterface
{
    
    protected $baseValue;


    
    public function setValue($value)
    {
        $this->baseValue = $value;
    }

    
    public function hasValue()
    {
        return !empty($this->baseValue) || ($this->baseValue === '0');
    }
}
