<?php


namespace aabc\rest;

use Aabc;
use aabc\base\Model;
use aabc\db\ActiveRecord;
use aabc\web\ServerErrorHttpException;


class UpdateAction extends Action
{
    
    public $scenario = Model::SCENARIO_DEFAULT;


    
    public function run($id)
    {
        /* @var $model ActiveRecord */
        $model = $this->findModel($id);

        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        $model->scenario = $this->scenario;
        $model->load(Aabc::$app->getRequest()->getBodyParams(), '');
        if ($model->save() === false && !$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        return $model;
    }
}
