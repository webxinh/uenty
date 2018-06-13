<?php


namespace aabc\rbac;

use aabc\base\Component;
use aabc\base\InvalidConfigException;
use aabc\base\InvalidParamException;


abstract class BaseManager extends Component implements ManagerInterface
{
    
    public $defaultRoles = [];


    
    abstract protected function getItem($name);

    
    abstract protected function getItems($type);

    
    abstract protected function addItem($item);

    
    abstract protected function addRule($rule);

    
    abstract protected function removeItem($item);

    
    abstract protected function removeRule($rule);

    
    abstract protected function updateItem($name, $item);

    
    abstract protected function updateRule($name, $rule);

    
    public function createRole($name)
    {
        $role = new Role();
        $role->name = $name;
        return $role;
    }

    
    public function createPermission($name)
    {
        $permission = new Permission();
        $permission->name = $name;
        return $permission;
    }

    
    public function add($object)
    {
        if ($object instanceof Item) {
            if ($object->ruleName && $this->getRule($object->ruleName) === null) {
                $rule = \Aabc::createObject($object->ruleName);
                $rule->name = $object->ruleName;
                $this->addRule($rule);
            }
            return $this->addItem($object);
        } elseif ($object instanceof Rule) {
            return $this->addRule($object);
        } else {
            throw new InvalidParamException('Adding unsupported object type.');
        }
    }

    
    public function remove($object)
    {
        if ($object instanceof Item) {
            return $this->removeItem($object);
        } elseif ($object instanceof Rule) {
            return $this->removeRule($object);
        } else {
            throw new InvalidParamException('Removing unsupported object type.');
        }
    }

    
    public function update($name, $object)
    {
        if ($object instanceof Item) {
            if ($object->ruleName && $this->getRule($object->ruleName) === null) {
                $rule = \Aabc::createObject($object->ruleName);
                $rule->name = $object->ruleName;
                $this->addRule($rule);
            }
            return $this->updateItem($name, $object);
        } elseif ($object instanceof Rule) {
            return $this->updateRule($name, $object);
        } else {
            throw new InvalidParamException('Updating unsupported object type.');
        }
    }

    
    public function getRole($name)
    {
        $item = $this->getItem($name);
        return $item instanceof Item && $item->type == Item::TYPE_ROLE ? $item : null;
    }

    
    public function getPermission($name)
    {
        $item = $this->getItem($name);
        return $item instanceof Item && $item->type == Item::TYPE_PERMISSION ? $item : null;
    }

    
    public function getRoles()
    {
        return $this->getItems(Item::TYPE_ROLE);
    }

    
    public function getPermissions()
    {
        return $this->getItems(Item::TYPE_PERMISSION);
    }

    
    protected function executeRule($user, $item, $params)
    {
        if ($item->ruleName === null) {
            return true;
        }
        $rule = $this->getRule($item->ruleName);
        if ($rule instanceof Rule) {
            return $rule->execute($user, $item, $params);
        } else {
            throw new InvalidConfigException("Rule not found: {$item->ruleName}");
        }
    }

    
    protected function hasNoAssignments(array $assignments)
    {
        return empty($assignments) && empty($this->defaultRoles);
    }
}
