<?php


namespace aabc\debug\components\search\matchers;


interface MatcherInterface
{
    
    public function match($value);

    
    public function setValue($value);

    
    public function hasValue();
}
