<?php
namespace Codeception\Lib\Interfaces;

interface Remote
{
    
    public function amOnSubdomain($subdomain);

    
    public function amOnUrl($url);

    public function _getUrl();
}
