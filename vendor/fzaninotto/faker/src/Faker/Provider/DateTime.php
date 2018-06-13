<?php

namespace Faker\Provider;

class DateTime extends Base
{
    protected static $century = array('I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII','XIII','XIV','XV','XVI','XVII','XVIII','XIX','XX','XXI');

    protected static function getMaxTimestamp($max = 'now')
    {
        if (is_numeric($max)) {
            return (int) $max;
        }

        if ($max instanceof \DateTime) {
            return $max->getTimestamp();
        }

        return strtotime(empty($max) ? 'now' : $max);
    }

    
    public static function unixTime($max = 'now')
    {
        return mt_rand(0, static::getMaxTimestamp($max));
    }

    
    public static function dateTime($max = 'now', $timezone = null)
    {
        return static::setTimezone(
            new \DateTime('@' . static::unixTime($max)),
            (null === $timezone ? date_default_timezone_get() : $timezone)
        );
    }

    
    public static function dateTimeAD($max = 'now', $timezone = null)
    {
        return static::setTimezone(
            new \DateTime('@' . mt_rand(-62135597361, static::getMaxTimestamp($max))),
            (null === $timezone ? date_default_timezone_get() : $timezone)
        );
    }

    
    public static function iso8601($max = 'now')
    {
        return static::date(\DateTime::ISO8601, $max);
    }

    
    public static function date($format = 'Y-m-d', $max = 'now')
    {
        return static::dateTime($max)->format($format);
    }

    
    public static function time($format = 'H:i:s', $max = 'now')
    {
        return static::dateTime($max)->format($format);
    }

    
    public static function dateTimeBetween($startDate = '-30 years', $endDate = 'now', $timezone = null)
    {
        $startTimestamp = $startDate instanceof \DateTime ? $startDate->getTimestamp() : strtotime($startDate);
        $endTimestamp = static::getMaxTimestamp($endDate);

        if ($startTimestamp > $endTimestamp) {
            throw new \InvalidArgumentException('Start date must be anterior to end date.');
        }

        $timestamp = mt_rand($startTimestamp, $endTimestamp);

        return static::setTimezone(
            new \DateTime('@' . $timestamp),
            (null === $timezone ? date_default_timezone_get() : $timezone)
        );
    }

    
    public static function dateTimeInInterval($date = '-30 years', $interval = '+5 days', $timezone = null)
    {
        $intervalObject = \DateInterval::createFromDateString($interval);
        $datetime       = $date instanceof \DateTime ? $date : new \DateTime($date);
        $otherDatetime  = clone $datetime;
        $otherDatetime->add($intervalObject);
        
        $begin = $datetime > $otherDatetime ? $otherDatetime : $datetime;
        $end = $datetime===$begin ? $otherDatetime : $datetime;

        return static::dateTimeBetween(
            $begin,
            $end,
            (null === $timezone ? date_default_timezone_get() : $timezone)
        );
    }

    
    public static function dateTimeThisCentury($max = 'now')
    {
        return static::dateTimeBetween('-100 year', $max);
    }

    
    public static function dateTimeThisDecade($max = 'now')
    {
        return static::dateTimeBetween('-10 year', $max);
    }

    
    public static function dateTimeThisYear($max = 'now')
    {
        return static::dateTimeBetween('-1 year', $max);
    }

    
    public static function dateTimeThisMonth($max = 'now')
    {
        return static::dateTimeBetween('-1 month', $max);
    }

    
    public static function amPm($max = 'now')
    {
        return static::dateTime($max)->format('a');
    }

    
    public static function dayOfMonth($max = 'now')
    {
        return static::dateTime($max)->format('d');
    }

    
    public static function dayOfWeek($max = 'now')
    {
        return static::dateTime($max)->format('l');
    }

    
    public static function month($max = 'now')
    {
        return static::dateTime($max)->format('m');
    }

    
    public static function monthName($max = 'now')
    {
        return static::dateTime($max)->format('F');
    }

    
    public static function year($max = 'now')
    {
        return static::dateTime($max)->format('Y');
    }

    
    public static function century()
    {
        return static::randomElement(static::$century);
    }

    
    public static function timezone()
    {
        return static::randomElement(\DateTimeZone::listIdentifiers());
    }

    
    private static function setTimezone(\DateTime $dt, $timezone)
    {
        return $dt->setTimezone(new \DateTimeZone($timezone));
    }
}
