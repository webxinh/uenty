<?php


namespace aabc\rest;

use Aabc;
use aabc\data\ActiveDataProvider;


class IndexAction extends Action
{
    
    public $prepareDataProvider;


    
    public function run()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        return $this->prepareDataProvider();
    }

    
    protected function prepareDataProvider()
    {
        if ($this->prepareDataProvider !== null) {
            return call_user_func($this->prepareDataProvider, $this);
        }

        /* @var $modelClass \aabc\db\BaseActiveRecord */
        $modelClass = $this->modelClass;

        return Aabc::createObject([
            'class' => ActiveDataProvider::className(),
            'query' => $modelClass::find(),
        ]);
    }
}
