<?php


namespace aabc\filters\auth;


class HttpBasicAuth extends AuthMethod
{
    
    public $realm = 'api';
    
    public $auth;


    
    public function authenticate($user, $request, $response)
    {
        $username = $request->getAuthUser();
        $password = $request->getAuthPassword();

        if ($this->auth) {
            if ($username !== null || $password !== null) {
                $identity = call_user_func($this->auth, $username, $password);
                if ($identity !== null) {
                    $user->switchIdentity($identity);
                } else {
                    $this->handleFailure($response);
                }
                return $identity;
            }
        } elseif ($username !== null) {
            $identity = $user->loginByAccessToken($username, get_class($this));
            if ($identity === null) {
                $this->handleFailure($response);
            }
            return $identity;
        }

        return null;
    }

    
    public function challenge($response)
    {
        $response->getHeaders()->set('WWW-Authenticate', "Basic realm=\"{$this->realm}\"");
    }
}
