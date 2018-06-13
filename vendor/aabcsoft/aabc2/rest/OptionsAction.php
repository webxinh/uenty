<?php


namespace aabc\rest;

use Aabc;


class OptionsAction extends \aabc\base\Action
{
    
    public $collectionOptions = ['GET', 'POST', 'HEAD', 'OPTIONS'];
    
    public $resourceOptions = ['GET', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];


    
    public function run($id = null)
    {
        if (Aabc::$app->getRequest()->getMethod() !== 'OPTIONS') {
            Aabc::$app->getResponse()->setStatusCode(405);
        }
        $options = $id === null ? $this->collectionOptions : $this->resourceOptions;
        Aabc::$app->getResponse()->getHeaders()->set('Allow', implode(', ', $options));
    }
}
