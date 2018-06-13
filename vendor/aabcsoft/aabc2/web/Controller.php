<?php


namespace aabc\web;

use Aabc;
use aabc\base\InlineAction;
use aabc\helpers\Url;


class Controller extends \aabc\base\Controller
{
    
    public $enableCsrfValidation = true;
    
    public $actionParams = [];


    
    public function renderAjax($view, $params = [])
    {
        return $this->getView()->renderAjax($view, $params, $this);
    }

    
    public function asJson($data)
    {
        $response = Aabc::$app->getResponse();
        $response->format = Response::FORMAT_JSON;
        $response->data = $data;
        return $response;
    }

    
    public function asXml($data)
    {
        $response = Aabc::$app->getResponse();
        $response->format = Response::FORMAT_XML;
        $response->data = $data;
        return $response;
    }

    
    public function bindActionParams($action, $params)
    {
        if ($action instanceof InlineAction) {
            $method = new \ReflectionMethod($this, $action->actionMethod);
        } else {
            $method = new \ReflectionMethod($action, 'run');
        }

        $args = [];
        $missing = [];
        $actionParams = [];
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $params)) {
                if ($param->isArray()) {
                    $args[] = $actionParams[$name] = (array) $params[$name];
                } elseif (!is_array($params[$name])) {
                    $args[] = $actionParams[$name] = $params[$name];
                } else {
                    throw new BadRequestHttpException(Aabc::t('aabc', 'Invalid data received for parameter "{param}".', [
                        'param' => $name,
                    ]));
                }
                unset($params[$name]);
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $actionParams[$name] = $param->getDefaultValue();
            } else {
                $missing[] = $name;
            }
        }

        if (!empty($missing)) {
            throw new BadRequestHttpException(Aabc::t('aabc', 'Missing required parameters: {params}', [
                'params' => implode(', ', $missing),
            ]));
        }

        $this->actionParams = $actionParams;

        return $args;
    }

    
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if ($this->enableCsrfValidation && Aabc::$app->getErrorHandler()->exception === null && !Aabc::$app->getRequest()->validateCsrfToken()) {
                throw new BadRequestHttpException(Aabc::t('aabc', 'Unable to verify your data submission.'));
            }
            return true;
        }
        
        return false;
    }

    
    public function redirect($url, $statusCode = 302)
    {
        return Aabc::$app->getResponse()->redirect(Url::to($url), $statusCode);
    }

    
    public function goHome()
    {
        return Aabc::$app->getResponse()->redirect(Aabc::$app->getHomeUrl());
    }

    
    public function goBack($defaultUrl = null)
    {
        return Aabc::$app->getResponse()->redirect(Aabc::$app->getUser()->getReturnUrl($defaultUrl));
    }

    
    public function refresh($anchor = '')
    {
        return Aabc::$app->getResponse()->redirect(Aabc::$app->getRequest()->getUrl() . $anchor);
    }
}
