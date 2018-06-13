<?php
namespace Codeception\Lib\Connector\Aabc2;

use aabc\test\FixtureTrait;

class FixturesStore
{
    use FixtureTrait;

    protected $data;

    
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function fixtures()
    {
        return $this->data;
    }
}
