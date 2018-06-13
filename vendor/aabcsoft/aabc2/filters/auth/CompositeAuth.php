<?php


namespace aabc\filters\auth;

use Aabc;
use aabc\base\InvalidConfigException;


class CompositeAuth extends AuthMethod
{
    
    public $authMethods = [];


    
    public function beforeAction($action)
    {
        return empty($this->authMethods) ? true : parent::beforeAction($action);
    }

    
    public function authenticate($user, $request, $response)
    {
        foreach ($this->authMethods as $i => $auth) {
            if (!$auth instanceof AuthInterface) {
                $this->authMethods[$i] = $auth = Aabc::createObject($auth);
                if (!$auth instanceof AuthInterface) {
                    throw new InvalidConfigException(get_class($auth) . ' must implement aabc\filters\auth\AuthInterface');
                }
            }

            $identity = $auth->authenticate($user, $request, $response);
            if ($identity !== null) {
                return $identity;
            }
        }

        return null;
    }

    
    public function challenge($response)
    {
        foreach ($this->authMethods as $method) {
            
            $method->challenge($response);
        }
    }
}
