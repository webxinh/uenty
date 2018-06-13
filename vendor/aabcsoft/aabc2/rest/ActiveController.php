<?php


namespace aabc\rest;

use aabc\base\InvalidConfigException;
use aabc\base\Model;
use aabc\web\ForbiddenHttpException;


class ActiveController extends Controller
{
    
    public $modelClass;
    
    public $updateScenario = Model::SCENARIO_DEFAULT;
    
    public $createScenario = Model::SCENARIO_DEFAULT;


    
    public function init()
    {
        parent::init();
        if ($this->modelClass === null) {
            throw new InvalidConfigException('The "modelClass" property must be set.');
        }
    }

    
    public function actions()
    {
        return [
            'index' => [
                'class' => 'aabc\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'view' => [
                'class' => 'aabc\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'create' => [
                'class' => 'aabc\rest\CreateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->createScenario,
            ],
            'update' => [
                'class' => 'aabc\rest\UpdateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->updateScenario,
            ],
            'delete' => [
                'class' => 'aabc\rest\DeleteAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'options' => [
                'class' => 'aabc\rest\OptionsAction',
            ],
        ];
    }

    
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    
    public function checkAccess($action, $model = null, $params = [])
    {
    }
}
