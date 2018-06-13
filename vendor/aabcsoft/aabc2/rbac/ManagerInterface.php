<?php


namespace aabc\rbac;


interface ManagerInterface extends CheckAccessInterface
{
    
    public function createRole($name);

    
    public function createPermission($name);

    
    public function add($object);

    
    public function remove($object);

    
    public function update($name, $object);

    
    public function getRole($name);

    
    public function getRoles();

    
    public function getRolesByUser($userId);

    
    public function getChildRoles($roleName);

    
    public function getPermission($name);

    
    public function getPermissions();

    
    public function getPermissionsByRole($roleName);

    
    public function getPermissionsByUser($userId);

    
    public function getRule($name);

    
    public function getRules();

    
    public function canAddChild($parent, $child);

    
    public function addChild($parent, $child);

    
    public function removeChild($parent, $child);

    
    public function removeChildren($parent);

    
    public function hasChild($parent, $child);

    
    public function getChildren($name);

    
    public function assign($role, $userId);

    
    public function revoke($role, $userId);

    
    public function revokeAll($userId);

    
    public function getAssignment($roleName, $userId);

    
    public function getAssignments($userId);

    
    public function getUserIdsByRole($roleName);

    
    public function removeAll();

    
    public function removeAllPermissions();

    
    public function removeAllRoles();

    
    public function removeAllRules();

    
    public function removeAllAssignments();
}
