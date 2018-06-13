<?php


namespace aabc\debug\models\search;

use aabc\data\ArrayDataProvider;
use aabc\debug\components\search\Filter;


class Mail extends Base
{
    
    public $from;
    
    public $to;
    
    public $reply;
    
    public $cc;
    
    public $bcc;
    
    public $subject;
    
    public $body;
    
    public $charset;
    
    public $headers;
    
    public $file;


    
    public function rules()
    {
        return [
            [['from', 'to', 'reply', 'cc', 'bcc', 'subject', 'body', 'charset'], 'safe'],
        ];
    }

    
    public function attributeLabels()
    {
        return [
            'from' => 'From',
            'to' => 'To',
            'reply' => 'Reply',
            'cc' => 'Copy receiver',
            'bcc' => 'Hidden copy receiver',
            'subject' => 'Subject',
            'charset' => 'Charset'
        ];
    }

    
    public function search($params, $models)
    {
        $dataProvider = new ArrayDataProvider([
            'allModels' => $models,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => ['from', 'to', 'reply', 'cc', 'bcc', 'subject', 'body', 'charset'],
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $filter = new Filter();
        $this->addCondition($filter, 'from', true);
        $this->addCondition($filter, 'to', true);
        $this->addCondition($filter, 'reply', true);
        $this->addCondition($filter, 'cc', true);
        $this->addCondition($filter, 'bcc', true);
        $this->addCondition($filter, 'subject', true);
        $this->addCondition($filter, 'body', true);
        $this->addCondition($filter, 'charset', true);
        $dataProvider->allModels = $filter->filter($models);

        return $dataProvider;
    }
}
