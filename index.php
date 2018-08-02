<?php
ob_start('ob_gzhandler');

defined('AABC_DEBUG') or define('AABC_DEBUG', true);
defined('AABC_ENV') or define('AABC_ENV', 'dev');

require('index-template.php');

require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/aabcsoft/aabc2/Aabc.php');
require(__DIR__ . '/common/config/bootstrap.php');
require(__DIR__ . '/frontend/config/bootstrap.php');

$config = aabc\helpers\ArrayHelper::merge(
    require(__DIR__ . '/common/config/main.php'),
    require(__DIR__ . '/common/config/main-local.php'),
    require(__DIR__ . '/frontend/config/main.php'),
    require(__DIR__ . '/frontend/config/main-local.php')
);

(new aabc\web\Application($config))->run();
