<?php
/* @var $panel aabc\debug\panels\LogPanel */
/* @var $searchModel aabc\debug\models\search\Log */
/* @var $dataProvider aabc\data\ArrayDataProvider */

use aabc\helpers\Html;
use aabc\grid\GridView;
use aabc\helpers\VarDumper;
use aabc\log\Logger;

?>
<h1>Log Messages</h1>
<?php

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'id' => 'log-panel-detailed-grid',
    'options' => ['class' => 'detail-grid-view table-responsive'],
    'filterModel' => $searchModel,
    'filterUrl' => $panel->getUrl(),
    'rowOptions' => function ($model, $key, $index, $grid) {
        switch ($model['level']) {
            case Logger::LEVEL_ERROR : return ['class' => 'danger'];
            case Logger::LEVEL_WARNING : return ['class' => 'warning'];
            case Logger::LEVEL_INFO : return ['class' => 'success'];
            default: return [];
        }
    },
    'columns' => [
        [
            'attribute' => 'time',
            'value' => function ($data) {
                $timeInSeconds = $data['time'] / 1000;
                $millisecondsDiff = (int) (($timeInSeconds - (int) $timeInSeconds) * 1000);

                return date('H:i:s.', $timeInSeconds) . sprintf('%03d', $millisecondsDiff);
            },
            'headerOptions' => [
                'class' => 'sort-numerical'
            ]
        ],
        [
            'attribute' => 'level',
            'value' => function ($data) {
                return Logger::getLevelName($data['level']);
            },
            'filter' => [
                Logger::LEVEL_TRACE => ' Trace ',
                Logger::LEVEL_INFO => ' Info ',
                Logger::LEVEL_WARNING => ' Warning ',
                Logger::LEVEL_ERROR => ' Error ',
            ],
        ],
        'category',
        [
            'attribute' => 'message',
            'value' => function ($data) use ($panel) {

                if(Html::encode(is_string($data['message']) ) ) {
                    $message = $data['message'];
                    $s = 'C:\x7\htdocs\20170715';
                    $v = strpos($message,$s);
                    $return = '';
                    $s2 = '';
                    $dem = 0;
                    while($v !== false){
                        $s1 = substr($message,0, $v);
                        $s2 = substr($message,$v + strlen($s), strlen($message));
                        $v2 = strpos($s2,'.php');
                        $a = substr($s2,0, $v2);

                        $s_exten = substr($s2,$v2 + 5,10);
                        $s_exten = intval($s_exten);

                        $s2 = substr($s2,$v2 + 4, strlen($s2));                        
                        
                        $link = $s . $a .'.php';
                        $link = str_replace('\\','/',$link);
                        $link = '<a href="subl://'.$link.':'.$s_exten.'">'.$link.'</a>';
                        $return .= $s1 . $link;
                        $message = $s2;
                        $v = strpos($message,$s);
                        // $a = str_replace('C:\x7\htdocs\20170715', $v, $a);
                        $dem += 1;
                    }
                    if($dem > 0){
                        $message = $return . $s2;
                    }
                }else{
                    $message = VarDumper::export($data['message']);
                }

                // $message = Html::encode(is_string($data['message']) ? $a : VarDumper::export($data['message']));
                if (!empty($data['trace'])) {
                    $message .= Html::ul($data['trace'], [
                        'class' => 'trace',
                        'item' => function ($trace) use ($panel) {
                            return '<li>' . $panel->getTraceLine($trace) . '</li>';
                        }
                    ]);
                };
                return $message;
            },
            'format' => 'raw',
            'options' => [
                'width' => '50%',
            ],
        ],
    ],
]);
