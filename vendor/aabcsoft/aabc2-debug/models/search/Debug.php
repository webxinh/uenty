<?php


namespace aabc\debug\models\search;

use aabc\data\ArrayDataProvider;
use aabc\debug\components\search\Filter;


class Debug extends Base
{
    
    public $tag;
    
    public $ip;
    
    public $method;
    
    public $ajax;
    
    public $url;
    
    public $statusCode;
    
    public $sqlCount;
    
    public $mailCount;
    
    public $criticalCodes = [400, 404, 500];


    
    public function rules()
    {
        return [
            [['tag', 'ip', 'method', 'ajax', 'url', 'statusCode', 'sqlCount', 'mailCount'], 'safe'],
        ];
    }

    
    public function attributeLabels()
    {
        return [
            'tag' => 'Tag',
            'ip' => 'Ip',
            'method' => 'Method',
            'ajax' => 'Ajax',
            'url' => 'url',
            'statusCode' => 'Status code',
            'sqlCount' => 'Query Count',
            'mailCount' => 'Mail Count',
        ];
    }

    
    public function search($params, $models)
    {
        $dataProvider = new ArrayDataProvider([
            'allModels' => $models,
            'sort' => [
                'attributes' => ['method', 'ip', 'tag', 'time', 'statusCode', 'sqlCount', 'mailCount'],
            ],
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $filter = new Filter();
        $this->addCondition($filter, 'tag', true);
        $this->addCondition($filter, 'ip', true);
        $this->addCondition($filter, 'method');
        $this->addCondition($filter, 'ajax');
        $this->addCondition($filter, 'url', true);
        $this->addCondition($filter, 'statusCode');
        $this->addCondition($filter, 'sqlCount');
        $this->addCondition($filter, 'mailCount');
        $dataProvider->allModels = $filter->filter($models);

        return $dataProvider;
    }

    
    public function isCodeCritical($code)
    {
        return in_array($code, $this->criticalCodes);
    }
}
