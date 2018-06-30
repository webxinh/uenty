<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Workaround for http://bugs.php.net/bug.php?id=47987,
// see https://github.com/sebastianbergmann/phpunit/issues#issue/125 for details
// Use dirname(__DIR__) instead of using /../ because of https://github.com/facebook/hhvm/issues/5215
require_once dirname(__DIR__) . '/Framework/Error.php';
require_once dirname(__DIR__) . '/Framework/Error/Notice.php';
require_once dirname(__DIR__) . '/Framework/Error/Warning.php';
require_once dirname(__DIR__) . '/Framework/Error/Deprecated.php';


class PHPUnit_Util_ErrorHandler
{
    protected static $errorStack = [];

    
    public static function getErrorStack()
    {
        return self::$errorStack;
    }

    
    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        if (!($errno & error_reporting())) {
            return false;
        }

        self::$errorStack[] = [$errno, $errstr, $errfile, $errline];

        $trace = debug_backtrace(false);
        array_shift($trace);

        foreach ($trace as $frame) {
            if ($frame['function'] == '__toString') {
                return false;
            }
        }

        if ($errno == E_NOTICE || $errno == E_USER_NOTICE || $errno == E_STRICT) {
            if (PHPUnit_Framework_Error_Notice::$enabled !== true) {
                return false;
            }

            $exception = 'PHPUnit_Framework_Error_Notice';
        } elseif ($errno == E_WARNING || $errno == E_USER_WARNING) {
            if (PHPUnit_Framework_Error_Warning::$enabled !== true) {
                return false;
            }

            $exception = 'PHPUnit_Framework_Error_Warning';
        } elseif ($errno == E_DEPRECATED || $errno == E_USER_DEPRECATED) {
            if (PHPUnit_Framework_Error_Deprecated::$enabled !== true) {
                return false;
            }

            $exception = 'PHPUnit_Framework_Error_Deprecated';
        } else {
            $exception = 'PHPUnit_Framework_Error';
        }

        throw new $exception($errstr, $errno, $errfile, $errline);
    }

    
    public static function handleErrorOnce($severity = E_WARNING)
    {
        $terminator = function () {
            static $expired = false;
            if (!$expired) {
                $expired = true;
                // cleans temporary error handler
                return restore_error_handler();
            }
        };

        set_error_handler(function ($errno, $errstr) use ($severity) {
            if ($errno === $severity) {
                return;
            }

            return false;
        });

        return $terminator;
    }
}