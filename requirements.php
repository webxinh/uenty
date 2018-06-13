<?php


// you may need to adjust this path to the correct Aabc framework path
$frameworkPath = dirname(__FILE__) . '/vendor/aabcsoft/aabc2';

if (!is_dir($frameworkPath)) {
    echo '<h1>Error</h1>';
    echo '<p><strong>The path to aabc framework seems to be incorrect.</strong></p>';
    echo '<p>You need to install Aabc framework via composer or adjust the framework path in file <abbr title="' . __FILE__ . '">' . basename(__FILE__) . '</abbr>.</p>';
    echo '<p>Please refer to the <abbr title="' . dirname(__FILE__) . '/README.md">README</abbr> on how to install Aabc.</p>';
}

require_once($frameworkPath . '/requirements/AabcRequirementChecker.php');
$requirementsChecker = new AabcRequirementChecker();

$gdMemo = $imagickMemo = 'Either GD PHP extension with FreeType support or ImageMagick PHP extension with PNG support is required for image CAPTCHA.';
$gdOK = $imagickOK = false;

if (extension_loaded('imagick')) {
    $imagick = new Imagick();
    $imagickFormats = $imagick->queryFormats('PNG');
    if (in_array('PNG', $imagickFormats)) {
        $imagickOK = true;
    } else {
        $imagickMemo = 'Imagick extension should be installed with PNG support in order to be used for image CAPTCHA.';
    }
}

if (extension_loaded('gd')) {
    $gdInfo = gd_info();
    if (!empty($gdInfo['FreeType Support'])) {
        $gdOK = true;
    } else {
        $gdMemo = 'GD extension should be installed with FreeType support in order to be used for image CAPTCHA.';
    }
}


$requirements = array(
    // Database :
    array(
        'name' => 'PDO extension',
        'mandatory' => true,
        'condition' => extension_loaded('pdo'),
        'by' => 'All DB-related classes',
    ),
    array(
        'name' => 'PDO SQLite extension',
        'mandatory' => false,
        'condition' => extension_loaded('pdo_sqlite'),
        'by' => 'All DB-related classes',
        'memo' => 'Required for SQLite database.',
    ),
    array(
        'name' => 'PDO MySQL extension',
        'mandatory' => false,
        'condition' => extension_loaded('pdo_mysql'),
        'by' => 'All DB-related classes',
        'memo' => 'Required for MySQL database.',
    ),
    array(
        'name' => 'PDO PostgreSQL extension',
        'mandatory' => false,
        'condition' => extension_loaded('pdo_pgsql'),
        'by' => 'All DB-related classes',
        'memo' => 'Required for PostgreSQL database.',
    ),
    // Cache :
    array(
        'name' => 'Memcache extension',
        'mandatory' => false,
        'condition' => extension_loaded('memcache') || extension_loaded('memcached'),
        'by' => '<a href="http://www.aabcframework.com/doc-2.0/aabc-caching-memcache.html">MemCache</a>',
        'memo' => extension_loaded('memcached') ? 'To use memcached set <a href="http://www.aabcframework.com/doc-2.0/aabc-caching-memcache.html#$useMemcached-detail">MemCache::useMemcached</a> to <code>true</code>.' : ''
    ),
    array(
        'name' => 'APC extension',
        'mandatory' => false,
        'condition' => extension_loaded('apc'),
        'by' => '<a href="http://www.aabcframework.com/doc-2.0/aabc-caching-apccache.html">ApcCache</a>',
    ),
    // CAPTCHA:
    array(
        'name' => 'GD PHP extension with FreeType support',
        'mandatory' => false,
        'condition' => $gdOK,
        'by' => '<a href="http://www.aabcframework.com/doc-2.0/aabc-captcha-captcha.html">Captcha</a>',
        'memo' => $gdMemo,
    ),
    array(
        'name' => 'ImageMagick PHP extension with PNG support',
        'mandatory' => false,
        'condition' => $imagickOK,
        'by' => '<a href="http://www.aabcframework.com/doc-2.0/aabc-captcha-captcha.html">Captcha</a>',
        'memo' => $imagickMemo,
    ),
    // PHP ini :
    'phpExposePhp' => array(
        'name' => 'Expose PHP',
        'mandatory' => false,
        'condition' => $requirementsChecker->checkPhpIniOff("expose_php"),
        'by' => 'Security reasons',
        'memo' => '"expose_php" should be disabled at php.ini',
    ),
    'phpAllowUrlInclude' => array(
        'name' => 'PHP allow url include',
        'mandatory' => false,
        'condition' => $requirementsChecker->checkPhpIniOff("allow_url_include"),
        'by' => 'Security reasons',
        'memo' => '"allow_url_include" should be disabled at php.ini',
    ),
    'phpSmtp' => array(
        'name' => 'PHP mail SMTP',
        'mandatory' => false,
        'condition' => strlen(ini_get('SMTP')) > 0,
        'by' => 'Email sending',
        'memo' => 'PHP mail SMTP server required',
    ),
);
$requirementsChecker->checkAabc()->check($requirements)->render();
