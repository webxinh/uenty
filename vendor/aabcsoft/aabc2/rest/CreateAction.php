<?php


namespace aabc\rest;

use Aabc;
use aabc\base\Model;
use aabc\helpers\Url;
use aabc\web\ServerErrorHttpException;


class CreateAction extends Action
{
    
    public $scenario = Model::SCENARIO_DEFAULT;
    
    public $viewAction = 'view';


    
    public function run()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        /* @var $model \aabc\db\ActiveRecord */
        $model = new $this->modelClass([
            'scenario' => $this->scenario,
        ]);

        $model->load(Aabc::$app->getRequest()->getBodyParams(), '');
        if ($model->save()) {
            $response = Aabc::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }
}
