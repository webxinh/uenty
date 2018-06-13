<?php


namespace aabc\web;

use Aabc;
use aabc\base\Action;
use aabc\base\ViewNotFoundException;


class ViewAction extends Action
{
    
    public $viewParam = 'view';
    
    public $defaultView = 'index';
    
    public $viewPrefix = 'pages';
    
    public $layout;


    
    public function run()
    {
        $viewName = $this->resolveViewName();
        $this->controller->actionParams[$this->viewParam] = Aabc::$app->request->get($this->viewParam);

        $controllerLayout = null;
        if ($this->layout !== null) {
            $controllerLayout = $this->controller->layout;
            $this->controller->layout = $this->layout;
        }

        try {
            $output = $this->render($viewName);

            if ($controllerLayout) {
                $this->controller->layout = $controllerLayout;
            }

        } catch (ViewNotFoundException $e) {

            if ($controllerLayout) {
                $this->controller->layout = $controllerLayout;
            }

            if (AABC_DEBUG) {
                throw new NotFoundHttpException($e->getMessage());
            } else {
                throw new NotFoundHttpException(
                    Aabc::t('aabc', 'The requested view "{name}" was not found.', ['name' => $viewName])
                );
            }
        }

        return $output;
    }

    
    protected function render($viewName)
    {
        return $this->controller->render($viewName);
    }

    
    protected function resolveViewName()
    {
        $viewName = Aabc::$app->request->get($this->viewParam, $this->defaultView);

        if (!is_string($viewName) || !preg_match('~^\w(?:(?!\/\.{0,2}\/)[\w\/\-\.])*$~', $viewName)) {
            if (AABC_DEBUG) {
                throw new NotFoundHttpException("The requested view \"$viewName\" must start with a word character, must not contain /../ or /./, can contain only word characters, forward slashes, dots and dashes.");
            } else {
                throw new NotFoundHttpException(Aabc::t('aabc', 'The requested view "{name}" was not found.', ['name' => $viewName]));
            }
        }

        return empty($this->viewPrefix) ? $viewName : $this->viewPrefix . '/' . $viewName;
    }
}
