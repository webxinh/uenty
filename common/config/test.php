<?php
return [
    'id' => 'app-common-tests',
    'basePath' => dirname(__DIR__),
    'components' => [
        'user' => [
            'class' => 'aabc\web\User',
            'identityClass' => 'common\models\User',
        ],
    ],
];
