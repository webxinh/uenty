<?php
return [
    'components' => [
        'db' => [
            'class' => 'aabc\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=aabc2advanced',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'aabc\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
        ],
    ],
];
