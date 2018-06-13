<?php


namespace aabc\filters;

use Aabc;
use aabc\base\ActionFilter;
use aabc\web\NotFoundHttpException;


class HostControl extends ActionFilter
{
    
    public $allowedHosts;
    
    public $denyCallback;
    
    public $fallbackHostInfo = '';


    
    public function beforeAction($action)
    {
        $allowedHosts = $this->allowedHosts;
        if ($allowedHosts instanceof \Closure) {
            $allowedHosts = call_user_func($allowedHosts, $action);
        }
        if ($allowedHosts === null) {
            return true;
        }

        if (!is_array($allowedHosts) && !$allowedHosts instanceof \Traversable) {
            $allowedHosts = (array)$allowedHosts;
        }

        $currentHost = Aabc::$app->getRequest()->getHostName();

        foreach ($allowedHosts as $allowedHost) {
            if (fnmatch($allowedHost, $currentHost)) {
                return true;
            }
        }

        // replace invalid host info to prevent using it in further processing
        if ($this->fallbackHostInfo !== null) {
            Aabc::$app->getRequest()->setHostInfo($this->fallbackHostInfo);
        }

        if ($this->denyCallback !== null) {
            call_user_func($this->denyCallback, $action);
        } else {
            $this->denyAccess($action);
        }

        return false;
    }

    
    protected function denyAccess($action)
    {
        $exception = new NotFoundHttpException(Aabc::t('aabc', 'Page not found.'));

        // use regular error handling if $this->fallbackHostInfo was set
        if (!empty(Aabc::$app->getRequest()->hostName)) {
            throw $exception;
        }

        $response = Aabc::$app->getResponse();
        $errorHandler = Aabc::$app->getErrorHandler();

        $response->setStatusCode($exception->statusCode, $exception->getMessage());
        $response->data = $errorHandler->renderFile($errorHandler->errorView, ['exception' => $exception]);
        $response->send();

        Aabc::$app->end();
    }
}
