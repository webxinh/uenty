<?php


namespace aabc\debug\components\search\matchers;


class GreaterThanOrEqual extends Base
{
    
    public function match($value)
    {
        return $value >= $this->baseValue;
    }
}
