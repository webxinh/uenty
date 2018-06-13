<?php


namespace aabc\caching;


class MemCacheServer extends \aabc\base\Object
{
    
    public $host;
    
    public $port = 11211;
    
    public $weight = 1;
    
    public $persistent = true;
    
    public $timeout = 1000;
    
    public $retryInterval = 15;
    
    public $status = true;
    
    public $failureCallback;
}
