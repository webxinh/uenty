<?php


namespace aabc\helpers;


class BaseHtmlPurifier
{
    
    public static function process($content, $config = null)
    {
        $configInstance = \HTMLPurifier_Config::create($config instanceof \Closure ? null : $config);
        $configInstance->autoFinalize = false;
        $purifier = \HTMLPurifier::instance($configInstance);
        $purifier->config->set('Cache.SerializerPath', \Aabc::$app->getRuntimePath());
        $purifier->config->set('Cache.SerializerPermissions', 0775);

        static::configure($configInstance);
        if ($config instanceof \Closure) {
            call_user_func($config, $configInstance);
        }

        return $purifier->purify($content);
    }

    
    protected static function configure($config)
    {
    }
}
