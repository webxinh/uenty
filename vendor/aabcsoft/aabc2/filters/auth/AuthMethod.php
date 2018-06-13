<?php


namespace aabc\filters\auth;

use Aabc;
use aabc\base\Action;
use aabc\base\ActionFilter;
use aabc\web\UnauthorizedHttpException;
use aabc\web\User;
use aabc\web\Request;
use aabc\web\Response;


abstract class AuthMethod extends ActionFilter implements AuthInterface
{
    
    public $user;
    
    public $request;
    
    public $response;
    
    public $optional = [];


    
    public function beforeAction($action)
    {
        $response = $this->response ? : Aabc::$app->getResponse();

        try {
            $identity = $this->authenticate(
                $this->user ? : Aabc::$app->getUser(),
                $this->request ? : Aabc::$app->getRequest(),
                $response
            );
        } catch (UnauthorizedHttpException $e) {
            if ($this->isOptional($action)) {
                return true;
            }

            throw $e;
        }

        if ($identity !== null || $this->isOptional($action)) {
            return true;
        } else {
            $this->challenge($response);
            $this->handleFailure($response);
            return false;
        }
    }

    
    public function challenge($response)
    {
    }

    
    public function handleFailure($response)
    {
        throw new UnauthorizedHttpException('Your request was made with invalid credentials.');
    }

    
    protected function isOptional($action)
    {
        $id = $this->getActionId($action);
        foreach ($this->optional as $pattern) {
            if (fnmatch($pattern, $id)) {
                return true;
            }
        }
        return false;
    }
}
