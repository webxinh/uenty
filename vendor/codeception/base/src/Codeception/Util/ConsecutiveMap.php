<?php

namespace Codeception\Util;


class ConsecutiveMap
{
    private $consecutiveMap = [];

    public function __construct(array $consecutiveMap)
    {
        $this->consecutiveMap = $consecutiveMap;
    }

    public function getMap()
    {
        return $this->consecutiveMap;
    }
}
