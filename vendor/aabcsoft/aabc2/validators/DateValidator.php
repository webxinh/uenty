<?php


namespace aabc\validators;

use DateTime;
use IntlDateFormatter;
use Aabc;
use aabc\base\InvalidConfigException;
use aabc\helpers\FormatConverter;


class DateValidator extends Validator
{
    
    const TYPE_DATE = 'date';
    
    const TYPE_DATETIME = 'datetime';
    
    const TYPE_TIME = 'time';

    
    public $type = self::TYPE_DATE;
    
    public $format;
    
    public $locale;
    
    public $timeZone;
    
    public $timestampAttribute;
    
    public $timestampAttributeFormat;
    
    public $timestampAttributeTimeZone = 'UTC';
    
    public $max;
    
    public $min;
    
    public $tooBig;
    
    public $tooSmall;
    
    public $maxString;
    
    public $minString;

    
    private $_dateFormats = [
        'short'  => 3, // IntlDateFormatter::SHORT,
        'medium' => 2, // IntlDateFormatter::MEDIUM,
        'long'   => 1, // IntlDateFormatter::LONG,
        'full'   => 0, // IntlDateFormatter::FULL,
    ];


    
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Aabc::t('aabc', 'The format of {attribute} is invalid.');
        }
        if ($this->format === null) {
            if ($this->type === self::TYPE_DATE) {
                $this->format = Aabc::$app->formatter->dateFormat;
            } elseif ($this->type === self::TYPE_DATETIME) {
                $this->format = Aabc::$app->formatter->datetimeFormat;
            } elseif ($this->type === self::TYPE_TIME) {
                $this->format = Aabc::$app->formatter->timeFormat;
            } else {
                throw new InvalidConfigException('Unknown validation type set for DateValidator::$type: ' . $this->type);
            }
        }
        if ($this->locale === null) {
            $this->locale = Aabc::$app->language;
        }
        if ($this->timeZone === null) {
            $this->timeZone = Aabc::$app->timeZone;
        }
        if ($this->min !== null && $this->tooSmall === null) {
            $this->tooSmall = Aabc::t('aabc', '{attribute} must be no less than {min}.');
        }
        if ($this->max !== null && $this->tooBig === null) {
            $this->tooBig = Aabc::t('aabc', '{attribute} must be no greater than {max}.');
        }
        if ($this->maxString === null) {
            $this->maxString = (string) $this->max;
        }
        if ($this->minString === null) {
            $this->minString = (string) $this->min;
        }
        if ($this->max !== null && is_string($this->max)) {
            $timestamp = $this->parseDateValue($this->max);
            if ($timestamp === false) {
                throw new InvalidConfigException("Invalid max date value: {$this->max}");
            }
            $this->max = $timestamp;
        }
        if ($this->min !== null && is_string($this->min)) {
            $timestamp = $this->parseDateValue($this->min);
            if ($timestamp === false) {
                throw new InvalidConfigException("Invalid min date value: {$this->min}");
            }
            $this->min = $timestamp;
        }
    }

    
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        $timestamp = $this->parseDateValue($value);
        if ($timestamp === false) {
            if ($this->timestampAttribute === $attribute) {
                if ($this->timestampAttributeFormat === null) {
                    if (is_int($value)) {
                        return;
                    }
                } else {
                    if ($this->parseDateValueFormat($value, $this->timestampAttributeFormat) !== false) {
                        return;
                    }
                }
            }
            $this->addError($model, $attribute, $this->message, []);
        } elseif ($this->min !== null && $timestamp < $this->min) {
            $this->addError($model, $attribute, $this->tooSmall, ['min' => $this->minString]);
        } elseif ($this->max !== null && $timestamp > $this->max) {
            $this->addError($model, $attribute, $this->tooBig, ['max' => $this->maxString]);
        } elseif ($this->timestampAttribute !== null) {
            if ($this->timestampAttributeFormat === null) {
                $model->{$this->timestampAttribute} = $timestamp;
            } else {
                $model->{$this->timestampAttribute} = $this->formatTimestamp($timestamp, $this->timestampAttributeFormat);
            }
        }
    }

    
    protected function validateValue($value)
    {
        $timestamp = $this->parseDateValue($value);
        if ($timestamp === false) {
            return [$this->message, []];
        } elseif ($this->min !== null && $timestamp < $this->min) {
            return [$this->tooSmall, ['min' => $this->minString]];
        } elseif ($this->max !== null && $timestamp > $this->max) {
            return [$this->tooBig, ['max' => $this->maxString]];
        } else {
            return null;
        }
    }

    
    protected function parseDateValue($value)
    {
        // TODO consider merging these methods into single one at 2.1
        return $this->parseDateValueFormat($value, $this->format);
    }

    
    private function parseDateValueFormat($value, $format)
    {
        if (is_array($value)) {
            return false;
        }
        if (strncmp($format, 'php:', 4) === 0) {
            $format = substr($format, 4);
        } else {
            if (extension_loaded('intl')) {
                return $this->parseDateValueIntl($value, $format);
            } else {
                // fallback to PHP if intl is not installed
                $format = FormatConverter::convertDateIcuToPhp($format, 'date');
            }
        }
        return $this->parseDateValuePHP($value, $format);
    }

    
    private function parseDateValueIntl($value, $format)
    {
        if (isset($this->_dateFormats[$format])) {
            if ($this->type === self::TYPE_DATE) {
                $formatter = new IntlDateFormatter($this->locale, $this->_dateFormats[$format], IntlDateFormatter::NONE, 'UTC');
            } elseif ($this->type === self::TYPE_DATETIME) {
                $formatter = new IntlDateFormatter($this->locale, $this->_dateFormats[$format], $this->_dateFormats[$format], $this->timeZone);
            } elseif ($this->type === self::TYPE_TIME) {
                $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, $this->_dateFormats[$format], $this->timeZone);
            } else {
                throw new InvalidConfigException('Unknown validation type set for DateValidator::$type: ' . $this->type);
            }
        } else {
            // if no time was provided in the format string set time to 0 to get a simple date timestamp
            $hasTimeInfo = (strpbrk($format, 'ahHkKmsSA') !== false);
            $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $hasTimeInfo ? $this->timeZone : 'UTC', null, $format);
        }
        // enable strict parsing to avoid getting invalid date values
        $formatter->setLenient(false);

        // There should not be a warning thrown by parse() but this seems to be the case on windows so we suppress it here
        // See https://github.com/aabcsoft/aabc2/issues/5962 and https://bugs.php.net/bug.php?id=68528
        $parsePos = 0;
        $parsedDate = @$formatter->parse($value, $parsePos);
        if ($parsedDate === false || $parsePos !== mb_strlen($value, Aabc::$app ? Aabc::$app->charset : 'UTF-8')) {
            return false;
        }

        return $parsedDate;
    }

    
    private function parseDateValuePHP($value, $format)
    {
        // if no time was provided in the format string set time to 0 to get a simple date timestamp
        $hasTimeInfo = (strpbrk($format, 'HhGgis') !== false);

        $date = DateTime::createFromFormat($format, $value, new \DateTimeZone($hasTimeInfo ? $this->timeZone : 'UTC'));
        $errors = DateTime::getLastErrors();
        if ($date === false || $errors['error_count'] || $errors['warning_count']) {
            return false;
        }

        if (!$hasTimeInfo) {
            $date->setTime(0, 0, 0);
        }
        return $date->getTimestamp();
    }

    
    private function formatTimestamp($timestamp, $format)
    {
        if (strncmp($format, 'php:', 4) === 0) {
            $format = substr($format, 4);
        } else {
            $format = FormatConverter::convertDateIcuToPhp($format, 'date');
        }

        $date = new DateTime();
        $date->setTimestamp($timestamp);
        $date->setTimezone(new \DateTimeZone($this->timestampAttributeTimeZone));
        return $date->format($format);
    }
}
