<?php



if (function_exists('spl_autoload_register')) {
    spl_autoload_register(array('HTMLPurifierExtras', 'autoload'));
    if (function_exists('__autoload')) {
        // Be polite and ensure that userland autoload gets retained
        spl_autoload_register('__autoload');
    }
} elseif (!function_exists('__autoload')) {
    function __autoload($class)
    {
        return HTMLPurifierExtras::autoload($class);
    }
}

// vim: et sw=4 sts=4
