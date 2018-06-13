<?php


namespace aabc\debug\components\search\matchers;


class GreaterThan extends Base
{
    
    public function match($value)
    {
        return ($value > $this->baseValue);
    }
}
