<?php


namespace aabc\filters;

use aabc\base\Component;
use aabc\base\Action;
use aabc\web\User;
use aabc\web\Request;
use aabc\base\Controller;


class AccessRule extends Component
{
    
    public $allow;
    
    public $actions;
    
    public $controllers;
    
    public $roles;
    
    public $ips;
    
    public $verbs;
    
    public $matchCallback;
    
    public $denyCallback;


    
    public function allows($action, $user, $request)
    {
        if ($this->matchAction($action)
            && $this->matchRole($user)
            && $this->matchIP($request->getUserIP())
            && $this->matchVerb($request->getMethod())
            && $this->matchController($action->controller)
            && $this->matchCustom($action)
        ) {
            return $this->allow ? true : false;
        } else {
            return null;
        }
    }

    
    protected function matchAction($action)
    {
        return empty($this->actions) || in_array($action->id, $this->actions, true);
    }

    
    protected function matchController($controller)
    {
        return empty($this->controllers) || in_array($controller->uniqueId, $this->controllers, true);
    }

    
    protected function matchRole($user)
    {
        if (empty($this->roles)) {
            return true;
        }
        foreach ($this->roles as $role) {
            if ($role === '?') {
                if ($user->getIsGuest()) {
                    return true;
                }
            } elseif ($role === '@') {
                if (!$user->getIsGuest()) {
                    return true;
                }
            } elseif ($user->can($role)) {
                return true;
            }
        }

        return false;
    }

    
    protected function matchIP($ip)
    {
        if (empty($this->ips)) {
            return true;
        }
        foreach ($this->ips as $rule) {
            if ($rule === '*' || $rule === $ip || (($pos = strpos($rule, '*')) !== false && !strncmp($ip, $rule, $pos))) {
                return true;
            }
        }

        return false;
    }

    
    protected function matchVerb($verb)
    {
        return empty($this->verbs) || in_array(strtoupper($verb), array_map('strtoupper', $this->verbs), true);
    }

    
    protected function matchCustom($action)
    {
        return empty($this->matchCallback) || call_user_func($this->matchCallback, $this, $action);
    }
}
