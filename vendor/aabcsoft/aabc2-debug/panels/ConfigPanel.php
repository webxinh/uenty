<?php


namespace aabc\debug\panels;

use Aabc;
use aabc\debug\Panel;


class ConfigPanel extends Panel
{
    
    public function getName()
    {
        return 'Configuration';
    }

    
    public function getSummary()
    {
        return Aabc::$app->view->render('panels/config/summary', ['panel' => $this]);
    }

    
    public function getDetail()
    {
        return Aabc::$app->view->render('panels/config/detail', ['panel' => $this]);
    }

    
    public function getExtensions()
    {
        $data = [];
        foreach ($this->data['extensions'] as $extension) {
            $data[$extension['name']] = $extension['version'];
        }
        ksort($data);

        return $data;
    }

    
    public function getPhpInfo()
    {
        ob_start();
        phpinfo();
        $pinfo = ob_get_contents();
        ob_end_clean();
        $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $pinfo);
        $phpinfo = str_replace('<table', '<div class="table-responsive"><table class="table table-condensed table-bordered table-striped table-hover config-php-info-table" ', $phpinfo);
        $phpinfo = str_replace('</table>', '</table></div>', $phpinfo);
        return $phpinfo;
    }

    
    public function save()
    {
        return [
            'phpVersion' => PHP_VERSION,
            'aabcVersion' => Aabc::getVersion(),
            'application' => [
                'aabc' => Aabc::getVersion(),
                'name' => Aabc::$app->name,
                'version' => Aabc::$app->version,
                'env' => AABC_ENV,
                'debug' => AABC_DEBUG,
            ],
            'php' => [
                'version' => PHP_VERSION,
                'xdebug' => extension_loaded('xdebug'),
                'apc' => extension_loaded('apc'),
                'memcache' => extension_loaded('memcache'),
                'memcached' => extension_loaded('memcached'),
            ],
            'extensions' => Aabc::$app->extensions,
        ];
    }
}
