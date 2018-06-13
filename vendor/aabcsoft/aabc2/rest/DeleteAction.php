<?php


namespace aabc\rest;

use Aabc;
use aabc\web\ServerErrorHttpException;


class DeleteAction extends Action
{
    
    public function run($id)
    {
        $model = $this->findModel($id);

        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        if ($model->delete() === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }

        Aabc::$app->getResponse()->setStatusCode(204);
    }
}
