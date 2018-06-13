<?php
namespace Codeception\Util;


class JsonType
{
    protected $jsonArray;

    protected static $customFilters = [];

    
    public function __construct($jsonArray)
    {
        if ($jsonArray instanceof JsonArray) {
            $jsonArray = $jsonArray->toArray();
        }
        $this->jsonArray = $jsonArray;
    }

    
    public static function addCustomFilter($name, callable $callable)
    {
        static::$customFilters[$name] = $callable;
    }

    
    public static function cleanCustomFilters()
    {
        static::$customFilters = [];
    }

    
    public function matches(array $jsonType)
    {
        if (array_key_exists(0, $this->jsonArray)) {
            // sequential array
            $msg = '';
            foreach ($this->jsonArray as $array) {
                $res = $this->typeComparison($array, $jsonType);
                if ($res !== true) {
                    $msg .= "\n" . $res;
                }
            }
            if ($msg) {
                return $msg;
            }
            return true;
        }
        return $this->typeComparison($this->jsonArray, $jsonType);
    }

    protected function typeComparison($data, $jsonType)
    {
        foreach ($jsonType as $key => $type) {
            if (!array_key_exists($key, $data)) {
                return "Key `$key` doesn't exist in " . json_encode($data);
            }
            if (is_array($jsonType[$key])) {
                $message = $this->typeComparison($data[$key], $jsonType[$key]);
                if (is_string($message)) {
                    return $message;
                }
                continue;
            }
            $matchTypes = preg_split("#(?![^]\(]*\))\|#", $type);
            $matched = false;
            $currentType = strtolower(gettype($data[$key]));
            if ($currentType == 'double') {
                $currentType = 'float';
            }
            foreach ($matchTypes as $matchType) {
                $filters = preg_split("#(?![^]\(]*\))\:#", $matchType);
                $expectedType = trim(strtolower(array_shift($filters)));

                if ($expectedType != $currentType) {
                    continue;
                }
                $matched = true;

                foreach ($filters as $filter) {
                    $matched = $matched && $this->matchFilter($filter, $data[$key]);
                }
                if ($matched) {
                    break;
                }
            }
            if (!$matched) {
                return sprintf("`$key: %s` is of type `$type`", var_export($data[$key], true));
            }
        }
        return true;
    }

    protected function matchFilter($filter, $value)
    {
        $filter = trim($filter);
        if (strpos($filter, '!') === 0) {
            return !$this->matchFilter(substr($filter, 1), $value);
        }

        // apply custom filters
        foreach (static::$customFilters as $customFilter => $callable) {
            if (strpos($customFilter, '/') === 0) {
                if (preg_match($customFilter, $filter, $matches)) {
                    array_shift($matches);
                    return call_user_func_array($callable, array_merge([$value], $matches));
                }
            }
            if ($customFilter == $filter) {
                return $callable($value);
            }
        }

        if (strpos($filter, '=') === 0) {
            return $value == substr($filter, 1);
        }
        if ($filter === 'url') {
            return filter_var($value, FILTER_VALIDATE_URL);
        }
        if ($filter === 'date') {
            return preg_match(
                '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(\.\d+)?(?:Z|(\+|-)([\d|:]*))?$/',
                $value
            );
        }
        if ($filter === 'email') { // from http://emailregex.com/
            // @codingStandardsIgnoreStart
            return preg_match('/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD', $value);
            // @codingStandardsIgnoreEnd
        }
        if ($filter === 'empty') {
            return empty($value);
        }
        if (preg_match('~^regex\((.*?)\)$~', $filter, $matches)) {
            return preg_match($matches[1], $value);
        }
        if (preg_match('~^>([\d\.]+)$~', $filter, $matches)) {
            return (float)$value > (float)$matches[1];
        }
        if (preg_match('~^<([\d\.]+)$~', $filter, $matches)) {
            return (float)$value < (float)$matches[1];
        }
    }
}
