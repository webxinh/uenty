<?php
ob_start('ob_gzhandler');
// ob_start(); 
// ob_flush();

defined('AABC_DEBUG') or define('AABC_DEBUG', true);
defined('AABC_ENV') or define('AABC_ENV', 'dev');
 
define('ADMIN','/ad/');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/aabcsoft/aabc2/Aabc.php');
require(__DIR__ . '/../common/config/bootstrap.php');
require(__DIR__ . '/../backend/config/bootstrap.php');
 
$config = aabc\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../common/config/main.php'),
    require(__DIR__ . '/../common/config/main-local.php'),
    require(__DIR__ . '/../backend/config/main.php'),
    require(__DIR__ . '/../backend/config/main-local.php')
);
 
$application = new aabc\web\Application($config);
$application->run();