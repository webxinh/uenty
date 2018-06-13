<?php


namespace aabc\filters;


interface RateLimitInterface
{
    
    public function getRateLimit($request, $action);

    
    public function loadAllowance($request, $action);

    
    public function saveAllowance($request, $action, $allowance, $timestamp);
}
