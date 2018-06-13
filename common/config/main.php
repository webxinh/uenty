<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'aabc\caching\FileCache',
        ],
        'dulieu' => [
            'class' => 'aabc\caching\FileCache',
            'cachePath' => Aabc::getAlias('@backend') . '/runtime/cache'
        ],
        'authManager' => [
        	'class' => 'aabc\rbac\DbManager'
        ]
    ],
];
