<?php


namespace aabc\rbac;


interface CheckAccessInterface
{
    
    public function checkAccess($userId, $permissionName, $params = []);
}
