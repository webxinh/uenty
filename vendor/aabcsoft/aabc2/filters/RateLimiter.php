<?php


namespace aabc\filters;

use Aabc;
use aabc\base\ActionFilter;
use aabc\web\Request;
use aabc\web\Response;
use aabc\web\TooManyRequestsHttpException;


class RateLimiter extends ActionFilter
{
    
    public $enableRateLimitHeaders = true;
    
    public $errorMessage = 'Rate limit exceeded.';
    
    public $user;
    
    public $request;
    
    public $response;


    
    public function beforeAction($action)
    {
        $user = $this->user ? : (Aabc::$app->getUser() ? Aabc::$app->getUser()->getIdentity(false) : null);
        if ($user instanceof RateLimitInterface) {
            Aabc::trace('Check rate limit', __METHOD__);
            $this->checkRateLimit(
                $user,
                $this->request ? : Aabc::$app->getRequest(),
                $this->response ? : Aabc::$app->getResponse(),
                $action
            );
        } elseif ($user) {
            Aabc::info('Rate limit skipped: "user" does not implement RateLimitInterface.', __METHOD__);
        } else {
            Aabc::info('Rate limit skipped: user not logged in.', __METHOD__);
        }
        return true;
    }

    
    public function checkRateLimit($user, $request, $response, $action)
    {
        $current = time();

        list ($limit, $window) = $user->getRateLimit($request, $action);
        list ($allowance, $timestamp) = $user->loadAllowance($request, $action);

        $allowance += (int) (($current - $timestamp) * $limit / $window);
        if ($allowance > $limit) {
            $allowance = $limit;
        }

        if ($allowance < 1) {
            $user->saveAllowance($request, $action, 0, $current);
            $this->addRateLimitHeaders($response, $limit, 0, $window);
            throw new TooManyRequestsHttpException($this->errorMessage);
        } else {
            $user->saveAllowance($request, $action, $allowance - 1, $current);
            $this->addRateLimitHeaders($response, $limit, $allowance - 1, (int) (($limit - $allowance) * $window / $limit));
        }
    }

    
    public function addRateLimitHeaders($response, $limit, $remaining, $reset)
    {
        if ($this->enableRateLimitHeaders) {
            $response->getHeaders()
                ->set('X-Rate-Limit-Limit', $limit)
                ->set('X-Rate-Limit-Remaining', $remaining)
                ->set('X-Rate-Limit-Reset', $reset);
        }
    }
}
