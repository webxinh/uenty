<?php
/*
 * This file is part of the PHP_Timer package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHP_Timer
{
    
    private static $times = array(
      'hour'   => 3600000,
      'minute' => 60000,
      'second' => 1000
    );

    
    private static $startTimes = array();

    
    public static $requestTime;

    
    public static function start()
    {
        array_push(self::$startTimes, microtime(true));
    }

    
    public static function stop()
    {
        return microtime(true) - array_pop(self::$startTimes);
    }

    
    public static function secondsToTimeString($time)
    {
        $ms = round($time * 1000);

        foreach (self::$times as $unit => $value) {
            if ($ms >= $value) {
                $time = floor($ms / $value * 100.0) / 100.0;

                return $time . ' ' . ($time == 1 ? $unit : $unit . 's');
            }
        }

        return $ms . ' ms';
    }

    
    public static function timeSinceStartOfRequest()
    {
        return self::secondsToTimeString(microtime(true) - self::$requestTime);
    }

    
    public static function resourceUsage()
    {
        return sprintf(
            'Time: %s, Memory: %4.2fMB',
            self::timeSinceStartOfRequest(),
            memory_get_peak_usage(true) / 1048576
        );
    }
}

if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
    PHP_Timer::$requestTime = $_SERVER['REQUEST_TIME_FLOAT'];
} elseif (isset($_SERVER['REQUEST_TIME'])) {
    PHP_Timer::$requestTime = $_SERVER['REQUEST_TIME'];
} else {
    PHP_Timer::$requestTime = microtime(true);
}
