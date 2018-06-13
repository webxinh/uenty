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
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
    ],
];
