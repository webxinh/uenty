<?php


namespace aabc\rest;

use Aabc;
use aabc\filters\auth\CompositeAuth;
use aabc\filters\ContentNegotiator;
use aabc\filters\RateLimiter;
use aabc\web\Response;
use aabc\filters\VerbFilter;


class Controller extends \aabc\web\Controller
{
    
    public $serializer = 'aabc\rest\Serializer';
    
    public $enableCsrfValidation = false;


    
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/xml' => Response::FORMAT_XML,
                ],
            ],
            'verbFilter' => [
                'class' => VerbFilter::className(),
                'actions' => $this->verbs(),
            ],
            'authenticator' => [
                'class' => CompositeAuth::className(),
            ],
            'rateLimiter' => [
                'class' => RateLimiter::className(),
            ],
        ];
    }

    
    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        return $this->serializeData($result);
    }

    
    protected function verbs()
    {
        return [];
    }

    
    protected function serializeData($data)
    {
        return Aabc::createObject($this->serializer)->serialize($data);
    }
}
