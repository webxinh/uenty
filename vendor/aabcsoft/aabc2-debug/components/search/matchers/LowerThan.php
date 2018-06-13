<?php


namespace aabc\debug\components\search\matchers;


class LowerThan extends Base
{
    
    public function match($value)
    {
        return ($value < $this->baseValue);
    }
}
