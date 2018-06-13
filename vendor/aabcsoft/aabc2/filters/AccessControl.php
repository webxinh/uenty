<?php


namespace aabc\filters;

use Aabc;
use aabc\base\Action;
use aabc\base\ActionFilter;
use aabc\di\Instance;
use aabc\web\User;
use aabc\web\ForbiddenHttpException;


class AccessControl extends ActionFilter
{
    
    public $user = 'user';
    
    public $denyCallback;
    
    public $ruleConfig = ['class' => 'aabc\filters\AccessRule'];
    
    public $rules = [];


    
    public function init()
    {
        parent::init();
        $this->user = Instance::ensure($this->user, User::className());
        foreach ($this->rules as $i => $rule) {
            if (is_array($rule)) {
                $this->rules[$i] = Aabc::createObject(array_merge($this->ruleConfig, $rule));
            }
        }
    }

    
    public function beforeAction($action)
    {
        $user = $this->user;
        $request = Aabc::$app->getRequest();
        /* @var $rule AccessRule */
        foreach ($this->rules as $rule) {
            if ($allow = $rule->allows($action, $user, $request)) {
                return true;
            } elseif ($allow === false) {
                if (isset($rule->denyCallback)) {
                    call_user_func($rule->denyCallback, $rule, $action);
                } elseif ($this->denyCallback !== null) {
                    call_user_func($this->denyCallback, $rule, $action);
                } else {
                    $this->denyAccess($user);
                }
                return false;
            }
        }
        if ($this->denyCallback !== null) {
            call_user_func($this->denyCallback, null, $action);
        } else {
            $this->denyAccess($user);
        }
        return false;
    }

    
    protected function denyAccess($user)
    {
        if ($user->getIsGuest()) {
            $user->loginRequired();
        } else {
            throw new ForbiddenHttpException(Aabc::t('aabc', 'You are not allowed to perform this action.'));
        }
    }
}
