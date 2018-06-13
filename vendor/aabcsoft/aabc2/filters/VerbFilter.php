<?php


namespace aabc\filters;

use Aabc;
use aabc\base\ActionEvent;
use aabc\base\Behavior;
use aabc\web\Controller;
use aabc\web\MethodNotAllowedHttpException;


class VerbFilter extends Behavior
{
    
    public $actions = [];


    
    public function events()
    {
        return [Controller::EVENT_BEFORE_ACTION => 'beforeAction'];
    }

    
    public function beforeAction($event)
    {
        $action = $event->action->id;
        if (isset($this->actions[$action])) {
            $verbs = $this->actions[$action];
        } elseif (isset($this->actions['*'])) {
            $verbs = $this->actions['*'];
        } else {
            return $event->isValid;
        }

        $verb = Aabc::$app->getRequest()->getMethod();
        $allowed = array_map('strtoupper', $verbs);
        if (!in_array($verb, $allowed)) {
            $event->isValid = false;
            // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.7
            Aabc::$app->getResponse()->getHeaders()->set('Allow', implode(', ', $allowed));
            throw new MethodNotAllowedHttpException('Method Not Allowed. This url can only handle the following request methods: ' . implode(', ', $allowed) . '.');
        }

        return $event->isValid;
    }
}
