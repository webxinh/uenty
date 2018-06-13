<?php


namespace aabc\debug\panels;

use Aabc;
use aabc\base\InlineAction;
use aabc\debug\Panel;


class RequestPanel extends Panel
{
    
    public function getName()
    {
        return 'Request';
    }

    
    public function getSummary()
    {
        return Aabc::$app->view->render('panels/request/summary', ['panel' => $this]);
    }

    
    public function getDetail()
    {
        return Aabc::$app->view->render('panels/request/detail', ['panel' => $this]);
    }

    
    public function save()
    {
        $headers = Aabc::$app->getRequest()->getHeaders();
        $requestHeaders = [];
        foreach ($headers as $name => $value) {
            if (is_array($value) && count($value) == 1) {
                $requestHeaders[$name] = current($value);
            } else {
                $requestHeaders[$name] = $value;
            }
        }

        $responseHeaders = [];
        foreach (headers_list() as $header) {
            if (($pos = strpos($header, ':')) !== false) {
                $name = substr($header, 0, $pos);
                $value = trim(substr($header, $pos + 1));
                if (isset($responseHeaders[$name])) {
                    if (!is_array($responseHeaders[$name])) {
                        $responseHeaders[$name] = [$responseHeaders[$name], $value];
                    } else {
                        $responseHeaders[$name][] = $value;
                    }
                } else {
                    $responseHeaders[$name] = $value;
                }
            } else {
                $responseHeaders[] = $header;
            }
        }
        if (Aabc::$app->requestedAction) {
            if (Aabc::$app->requestedAction instanceof InlineAction) {
                $action = get_class(Aabc::$app->requestedAction->controller) . '::' . Aabc::$app->requestedAction->actionMethod . '()';
            } else {
                $action = get_class(Aabc::$app->requestedAction) . '::run()';
            }
        } else {
            $action = null;
        }

        return [
            'flashes' => $this->getFlashes(),
            'statusCode' => Aabc::$app->getResponse()->getStatusCode(),
            'requestHeaders' => $requestHeaders,
            'responseHeaders' => $responseHeaders,
            'route' => Aabc::$app->requestedAction ? Aabc::$app->requestedAction->getUniqueId() : Aabc::$app->requestedRoute,
            'action' => $action,
            'actionParams' => Aabc::$app->requestedParams,
            'requestBody' => Aabc::$app->getRequest()->getRawBody() == '' ? [] : [
                'Content Type' => Aabc::$app->getRequest()->getContentType(),
                'Raw' => Aabc::$app->getRequest()->getRawBody(),
                'Decoded to Params' => Aabc::$app->getRequest()->getBodyParams(),
            ],
            'SERVER' => empty($_SERVER) ? [] : $_SERVER,
            'GET' => empty($_GET) ? [] : $_GET,
            'POST' => empty($_POST) ? [] : $_POST,
            'COOKIE' => empty($_COOKIE) ? [] : $_COOKIE,
            'FILES' => empty($_FILES) ? [] : $_FILES,
            'SESSION' => empty($_SESSION) ? [] : $_SESSION,
        ];
    }

    
    protected function getFlashes()
    {
        /* @var $session \aabc\web\Session */
        $session = Aabc::$app->has('session', true) ? Aabc::$app->get('session') : null;
        if ($session === null || !$session->getIsActive()) {
            return [];
        }

        $counters = $session->get($session->flashParam, []);
        $flashes = [];
        foreach (array_keys($counters) as $key) {
            if (array_key_exists($key, $_SESSION)) {
                $flashes[$key] = $_SESSION[$key];
            }
        }
        return $flashes;
    }
}
