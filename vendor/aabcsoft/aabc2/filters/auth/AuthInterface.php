<?php


namespace aabc\filters\auth;

use aabc\web\User;
use aabc\web\Request;
use aabc\web\Response;
use aabc\web\IdentityInterface;
use aabc\web\UnauthorizedHttpException;


interface AuthInterface
{
    
    public function authenticate($user, $request, $response);

    
    public function challenge($response);

    
    public function handleFailure($response);
}
