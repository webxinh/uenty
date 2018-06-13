<?php


namespace aabc\web;

use Aabc;
use aabc\base\Action;
use aabc\base\Exception;
use aabc\base\UserException;


class ErrorAction extends Action
{
    
    public $view;
    
    public $defaultName;
    
    public $defaultMessage;

    
    protected $exception;


    
    public function init()
    {
        $this->exception = $this->findException();

        if ($this->defaultMessage === null) {
            $this->defaultMessage = Aabc::t('aabc', 'An internal server error occurred.');
        }

        if ($this->defaultName === null) {
            $this->defaultName = Aabc::t('aabc', 'Error');
        }
    }

    
    public function run()
    {
        if (Aabc::$app->getRequest()->getIsAjax()) {
            return $this->renderAjaxResponse();
        }

        return $this->renderHtmlResponse();
    }

    
    protected function renderAjaxResponse()
    {
        return $this->getExceptionName() . ': ' . $this->getExceptionMessage();
    }

    
    protected function renderHtmlResponse()
    {
        return $this->controller->render($this->view ?: $this->id, $this->getViewRenderParams());
    }

    
    protected function getViewRenderParams()
    {
        return [
            'name' => $this->getExceptionName(),
            'message' => $this->getExceptionMessage(),
            'exception' => $this->exception,
        ];
    }

    
    protected function findException()
    {
        if (($exception = Aabc::$app->getErrorHandler()->exception) === null) {
            $exception = new NotFoundHttpException(Aabc::t('aabc', 'Page not found.'));
        }

        return $exception;
    }

    
    protected function getExceptionCode()
    {
        if ($this->exception instanceof HttpException) {
            return $this->exception->statusCode;
        }

        return $this->exception->getCode();
    }

    
    protected function getExceptionName()
    {
        if ($this->exception instanceof Exception) {
            $name = $this->exception->getName();
        } else {
            $name = $this->defaultName;
        }

        if ($code = $this->getExceptionCode()) {
            $name .= " (#$code)";
        }

        return $name;
    }

    
    protected function getExceptionMessage()
    {
        if ($this->exception instanceof UserException) {
            return $this->exception->getMessage();
        }

        return $this->defaultMessage;
    }
}
