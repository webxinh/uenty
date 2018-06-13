<?php
defined('AABC_DEBUG') or define('AABC_DEBUG', true);
defined('AABC_ENV') or define('AABC_ENV', 'test');
defined('AABC_APP_BASE_PATH') or define('AABC_APP_BASE_PATH', __DIR__.'/../../');

require_once(__DIR__ .  '/../../vendor/autoload.php');
require_once(__DIR__ .  '/../../vendor/aabcsoft/aabc2/Aabc.php');
require(__DIR__ . '/../config/bootstrap.php');

