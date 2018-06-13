<?php


namespace aabc\debug\components\search;

use aabc\base\Component;
use aabc\debug\components\search\matchers\MatcherInterface;


class Filter extends Component
{
    
    protected $rules = [];


    
    public function addMatcher($name, MatcherInterface $rule)
    {
        if ($rule->hasValue()) {
            $this->rules[$name][] = $rule;
        }
    }

    
    public function filter(array $data)
    {
        $filtered = [];

        foreach ($data as $row) {
            if ($this->passesFilter($row)) {
                $filtered[] = $row;
            }
        }

        return $filtered;
    }

    
    private function passesFilter(array $row)
    {
        foreach ($row as $name => $value) {
            if (isset($this->rules[$name])) {
                // check all rules for a given attribute
                foreach ($this->rules[$name] as $rule) {
                    /* @var $rule MatcherInterface */
                    if (!$rule->match($value)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}
