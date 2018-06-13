<?php

/*
 * This file is part of SwiftMailer.
 * (c) 2004-2009 Chris Corbyn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


abstract class Swift
{
    
    const VERSION = '@SWIFT_VERSION_NUMBER@';

    public static $initialized = false;
    public static $inits = array();

    
    public static function init($callable)
    {
        self::$inits[] = $callable;
    }

    
    public static function autoload($class)
    {
        // Don't interfere with other autoloaders
        if (0 !== strpos($class, 'Swift_')) {
            return;
        }

        $path = __DIR__.'/'.str_replace('_', '/', $class).'.php';

        if (!file_exists($path)) {
            return;
        }

        require $path;

        if (self::$inits && !self::$initialized) {
            self::$initialized = true;
            foreach (self::$inits as $init) {
                call_user_func($init);
            }
        }
    }

    
    public static function registerAutoload($callable = null)
    {
        if (null !== $callable) {
            self::$inits[] = $callable;
        }
        spl_autoload_register(array('Swift', 'autoload'));
    }
}
