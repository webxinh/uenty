<?php


namespace aabc\i18n;

use DateInterval;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use IntlDateFormatter;
use NumberFormatter;
use Aabc;
use aabc\base\Component;
use aabc\base\InvalidConfigException;
use aabc\base\InvalidParamException;
use aabc\helpers\FormatConverter;
use aabc\helpers\HtmlPurifier;
use aabc\helpers\Html;


class Formatter extends Component
{
    
    public $nullDisplay;
    
    public $booleanFormat;
    
    public $locale;
    
    public $timeZone;
    
    public $defaultTimeZone = 'UTC';
    
    public $dateFormat = 'medium';
    
    public $timeFormat = 'medium';
    
    public $datetimeFormat = 'medium';
    
    public $calendar;
    
    public $decimalSeparator;
    
    public $thousandSeparator;
    
    public $numberFormatterOptions = [];
    
    public $numberFormatterTextOptions = [];
    
    public $numberFormatterSymbols = [];
    
    public $currencyCode;
    
    public $sizeFormatBase = 1024;

    
    private $_intlLoaded = false;


    
    public function init()
    {
        if ($this->timeZone === null) {
            $this->timeZone = Aabc::$app->timeZone;
        }
        if ($this->locale === null) {
            $this->locale = Aabc::$app->language;
        }
        if ($this->booleanFormat === null) {
            $this->booleanFormat = [Aabc::t('aabc', 'No', [], $this->locale), Aabc::t('aabc', 'Yes', [], $this->locale)];
        }
        if ($this->nullDisplay === null) {
            $this->nullDisplay = '<span class="not-set">' . Aabc::t('aabc', '(not set)', [], $this->locale) . '</span>';
        }
        $this->_intlLoaded = extension_loaded('intl');
        if (!$this->_intlLoaded) {
            if ($this->decimalSeparator === null) {
                $this->decimalSeparator = '.';
            }
            if ($this->thousandSeparator === null) {
                $this->thousandSeparator = ',';
            }
        }
    }

    
    public function format($value, $format)
    {
        if (is_array($format)) {
            if (!isset($format[0])) {
                throw new InvalidParamException('The $format array must contain at least one element.');
            }
            $f = $format[0];
            $format[0] = $value;
            $params = $format;
            $format = $f;
        } else {
            $params = [$value];
        }
        $method = 'as' . $format;
        if ($this->hasMethod($method)) {
            return call_user_func_array([$this, $method], $params);
        } else {
            throw new InvalidParamException("Unknown format type: $format");
        }
    }


    // simple formats


    
    public function asRaw($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        return $value;
    }

    
    public function asText($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        return Html::encode($value);
    }

    
    public function asNtext($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        return nl2br(Html::encode($value));
    }

    
    public function asParagraphs($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        return str_replace('<p></p>', '', '<p>' . preg_replace('/\R{2,}/u', "</p>\n<p>", Html::encode($value)) . '</p>');
    }

    
    public function asHtml($value, $config = null)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        return HtmlPurifier::process($value, $config);
    }

    
    public function asEmail($value, $options = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        return Html::mailto(Html::encode($value), $value, $options);
    }

    
    public function asImage($value, $options = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        return Html::img($value, $options);
    }

    
    public function asUrl($value, $options = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $url = $value;
        if (strpos($url, '://') === false) {
            $url = 'http://' . $url;
        }

        return Html::a(Html::encode($value), $url, $options);
    }

    
    public function asBoolean($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return $value ? $this->booleanFormat[1] : $this->booleanFormat[0];
    }


    // date and time formats


    
    public function asDate($value, $format = null)
    {
        if ($format === null) {
            $format = $this->dateFormat;
        }
        return $this->formatDateTimeValue($value, $format, 'date');
    }

    
    public function asTime($value, $format = null)
    {
        if ($format === null) {
            $format = $this->timeFormat;
        }
        return $this->formatDateTimeValue($value, $format, 'time');
    }

    
    public function asDatetime($value, $format = null)
    {
        if ($format === null) {
            $format = $this->datetimeFormat;
        }
        return $this->formatDateTimeValue($value, $format, 'datetime');
    }

    
    private $_dateFormats = [
        'short'  => 3, // IntlDateFormatter::SHORT,
        'medium' => 2, // IntlDateFormatter::MEDIUM,
        'long'   => 1, // IntlDateFormatter::LONG,
        'full'   => 0, // IntlDateFormatter::FULL,
    ];

    
    private function formatDateTimeValue($value, $format, $type)
    {
        $timeZone = $this->timeZone;
        // avoid time zone conversion for date-only values
        if ($type === 'date') {
            list($timestamp, $hasTimeInfo) = $this->normalizeDatetimeValue($value, true);
            if (!$hasTimeInfo) {
                $timeZone = $this->defaultTimeZone;
            }
        } else {
            $timestamp = $this->normalizeDatetimeValue($value);
        }
        if ($timestamp === null) {
            return $this->nullDisplay;
        }

        // intl does not work with dates >=2038 or <=1901 on 32bit machines, fall back to PHP
        $year = $timestamp->format('Y');
        if ($this->_intlLoaded && !(PHP_INT_SIZE === 4 && ($year <= 1901 || $year >= 2038))) {
            if (strncmp($format, 'php:', 4) === 0) {
                $format = FormatConverter::convertDatePhpToIcu(substr($format, 4));
            }
            if (isset($this->_dateFormats[$format])) {
                if ($type === 'date') {
                    $formatter = new IntlDateFormatter($this->locale, $this->_dateFormats[$format], IntlDateFormatter::NONE, $timeZone, $this->calendar);
                } elseif ($type === 'time') {
                    $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, $this->_dateFormats[$format], $timeZone, $this->calendar);
                } else {
                    $formatter = new IntlDateFormatter($this->locale, $this->_dateFormats[$format], $this->_dateFormats[$format], $timeZone, $this->calendar);
                }
            } else {
                $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $timeZone, $this->calendar, $format);
            }
            if ($formatter === null) {
                throw new InvalidConfigException(intl_get_error_message());
            }
            // make IntlDateFormatter work with DateTimeImmutable
            if ($timestamp instanceof \DateTimeImmutable) {
                $timestamp = new DateTime($timestamp->format(DateTime::ISO8601), $timestamp->getTimezone());
            }
            return $formatter->format($timestamp);
        } else {
            if (strncmp($format, 'php:', 4) === 0) {
                $format = substr($format, 4);
            } else {
                $format = FormatConverter::convertDateIcuToPhp($format, $type, $this->locale);
            }
            if ($timeZone != null) {
                if ($timestamp instanceof \DateTimeImmutable) {
                    $timestamp = $timestamp->setTimezone(new DateTimeZone($timeZone));
                } else {
                    $timestamp->setTimezone(new DateTimeZone($timeZone));
                }
            }
            return $timestamp->format($format);
        }
    }

    
    protected function normalizeDatetimeValue($value, $checkTimeInfo = false)
    {
        // checking for DateTime and DateTimeInterface is not redundant, DateTimeInterface is only in PHP>5.5
        if ($value === null || $value instanceof DateTime || $value instanceof DateTimeInterface) {
            // skip any processing
            return $checkTimeInfo ? [$value, true] : $value;
        }
        if (empty($value)) {
            $value = 0;
        }
        try {
            if (is_numeric($value)) { // process as unix timestamp, which is always in UTC
                $timestamp = new DateTime('@' . (int)$value, new DateTimeZone('UTC'));
                return $checkTimeInfo ? [$timestamp, true] : $timestamp;
            } elseif (($timestamp = DateTime::createFromFormat('Y-m-d', $value, new DateTimeZone($this->defaultTimeZone))) !== false) { // try Y-m-d format (support invalid dates like 2012-13-01)
                return $checkTimeInfo ? [$timestamp, false] : $timestamp;
            } elseif (($timestamp = DateTime::createFromFormat('Y-m-d H:i:s', $value, new DateTimeZone($this->defaultTimeZone))) !== false) { // try Y-m-d H:i:s format (support invalid dates like 2012-13-01 12:63:12)
                return $checkTimeInfo ? [$timestamp, true] : $timestamp;
            }
            // finally try to create a DateTime object with the value
            if ($checkTimeInfo) {
                $timestamp = new DateTime($value, new DateTimeZone($this->defaultTimeZone));
                $info = date_parse($value);
                return [$timestamp, !($info['hour'] === false && $info['minute'] === false && $info['second'] === false)];
            } else {
                return new DateTime($value, new DateTimeZone($this->defaultTimeZone));
            }
        } catch (\Exception $e) {
            throw new InvalidParamException("'$value' is not a valid date time value: " . $e->getMessage()
                . "\n" . print_r(DateTime::getLastErrors(), true), $e->getCode(), $e);
        }
    }

    
    public function asTimestamp($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $timestamp = $this->normalizeDatetimeValue($value);
        return number_format($timestamp->format('U'), 0, '.', '');
    }

    
    public function asRelativeTime($value, $referenceTime = null)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        if ($value instanceof DateInterval) {
            $interval = $value;
        } else {
            $timestamp = $this->normalizeDatetimeValue($value);

            if ($timestamp === false) {
                // $value is not a valid date/time value, so we try
                // to create a DateInterval with it
                try {
                    $interval = new DateInterval($value);
                } catch (\Exception $e) {
                    // invalid date/time and invalid interval
                    return $this->nullDisplay;
                }
            } else {
                $timeZone = new DateTimeZone($this->timeZone);

                if ($referenceTime === null) {
                    $dateNow = new DateTime('now', $timeZone);
                } else {
                    $dateNow = $this->normalizeDatetimeValue($referenceTime);
                    $dateNow->setTimezone($timeZone);
                }

                $dateThen = $timestamp->setTimezone($timeZone);

                $interval = $dateThen->diff($dateNow);
            }
        }

        if ($interval->invert) {
            if ($interval->y >= 1) {
                return Aabc::t('aabc', 'in {delta, plural, =1{a year} other{# years}}', ['delta' => $interval->y], $this->locale);
            }
            if ($interval->m >= 1) {
                return Aabc::t('aabc', 'in {delta, plural, =1{a month} other{# months}}', ['delta' => $interval->m], $this->locale);
            }
            if ($interval->d >= 1) {
                return Aabc::t('aabc', 'in {delta, plural, =1{a day} other{# days}}', ['delta' => $interval->d], $this->locale);
            }
            if ($interval->h >= 1) {
                return Aabc::t('aabc', 'in {delta, plural, =1{an hour} other{# hours}}', ['delta' => $interval->h], $this->locale);
            }
            if ($interval->i >= 1) {
                return Aabc::t('aabc', 'in {delta, plural, =1{a minute} other{# minutes}}', ['delta' => $interval->i], $this->locale);
            }
            if ($interval->s == 0) {
                return Aabc::t('aabc', 'just now', [], $this->locale);
            }
            return Aabc::t('aabc', 'in {delta, plural, =1{a second} other{# seconds}}', ['delta' => $interval->s], $this->locale);
        } else {
            if ($interval->y >= 1) {
                return Aabc::t('aabc', '{delta, plural, =1{a year} other{# years}} ago', ['delta' => $interval->y], $this->locale);
            }
            if ($interval->m >= 1) {
                return Aabc::t('aabc', '{delta, plural, =1{a month} other{# months}} ago', ['delta' => $interval->m], $this->locale);
            }
            if ($interval->d >= 1) {
                return Aabc::t('aabc', '{delta, plural, =1{a day} other{# days}} ago', ['delta' => $interval->d], $this->locale);
            }
            if ($interval->h >= 1) {
                return Aabc::t('aabc', '{delta, plural, =1{an hour} other{# hours}} ago', ['delta' => $interval->h], $this->locale);
            }
            if ($interval->i >= 1) {
                return Aabc::t('aabc', '{delta, plural, =1{a minute} other{# minutes}} ago', ['delta' => $interval->i], $this->locale);
            }
            if ($interval->s == 0) {
                return Aabc::t('aabc', 'just now', [], $this->locale);
            }
            return Aabc::t('aabc', '{delta, plural, =1{a second} other{# seconds}} ago', ['delta' => $interval->s], $this->locale);
        }
    }

    
    public function asDuration($value, $implodeString = ', ', $negativeSign = '-')
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        if ($value instanceof DateInterval) {
            $isNegative = $value->invert;
            $interval = $value;
        } elseif (is_numeric($value)) {
            $isNegative = $value < 0;
            $zeroDateTime = (new DateTime())->setTimestamp(0);
            $valueDateTime = (new DateTime())->setTimestamp(abs($value));
            $interval = $valueDateTime->diff($zeroDateTime);
        } elseif (strpos($value, 'P-') === 0) {
            $interval = new DateInterval('P'.substr($value, 2));
            $isNegative = true;
        } else {
            $interval = new DateInterval($value);
            $isNegative = $interval->invert;
        }

        if ($interval->y > 0) {
            $parts[] = Aabc::t('aabc', '{delta, plural, =1{1 year} other{# years}}', ['delta' => $interval->y], $this->locale);
        }
        if ($interval->m > 0) {
            $parts[] = Aabc::t('aabc', '{delta, plural, =1{1 month} other{# months}}', ['delta' => $interval->m], $this->locale);
        }
        if ($interval->d > 0) {
            $parts[] = Aabc::t('aabc', '{delta, plural, =1{1 day} other{# days}}', ['delta' => $interval->d], $this->locale);
        }
        if ($interval->h > 0) {
            $parts[] = Aabc::t('aabc', '{delta, plural, =1{1 hour} other{# hours}}', ['delta' => $interval->h], $this->locale);
        }
        if ($interval->i > 0) {
            $parts[] = Aabc::t('aabc', '{delta, plural, =1{1 minute} other{# minutes}}', ['delta' => $interval->i], $this->locale);
        }
        if ($interval->s > 0) {
            $parts[] = Aabc::t('aabc', '{delta, plural, =1{1 second} other{# seconds}}', ['delta' => $interval->s], $this->locale);
        }
        if ($interval->s === 0 && empty($parts)) {
            $parts[] = Aabc::t('aabc', '{delta, plural, =1{1 second} other{# seconds}}', ['delta' => $interval->s], $this->locale);
            $isNegative = false;
        }

        return empty($parts) ? $this->nullDisplay : (($isNegative ? $negativeSign : '') . implode($implodeString, $parts));
    }


    // number formats


    
    public function asInteger($value, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);
        if ($this->_intlLoaded) {
            $f = $this->createNumberFormatter(NumberFormatter::DECIMAL, null, $options, $textOptions);
            $f->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);
            if (($result = $f->format($value, NumberFormatter::TYPE_INT64)) === false) {
                throw new InvalidParamException('Formatting integer value failed: ' . $f->getErrorCode() . ' ' . $f->getErrorMessage());
            }
            return $result;
        } else {
            return number_format((int) $value, 0, $this->decimalSeparator, $this->thousandSeparator);
        }
    }

    
    public function asDecimal($value, $decimals = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);

        if ($this->_intlLoaded) {
            $f = $this->createNumberFormatter(NumberFormatter::DECIMAL, $decimals, $options, $textOptions);
            if (($result = $f->format($value)) === false) {
                throw new InvalidParamException('Formatting decimal value failed: ' . $f->getErrorCode() . ' ' . $f->getErrorMessage());
            }
            return $result;
        } else {
            if ($decimals === null) {
                $decimals = 2;
            }
            return number_format($value, $decimals, $this->decimalSeparator, $this->thousandSeparator);
        }
    }


    
    public function asPercent($value, $decimals = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);

        if ($this->_intlLoaded) {
            $f = $this->createNumberFormatter(NumberFormatter::PERCENT, $decimals, $options, $textOptions);
            if (($result = $f->format($value)) === false) {
                throw new InvalidParamException('Formatting percent value failed: ' . $f->getErrorCode() . ' ' . $f->getErrorMessage());
            }
            return $result;
        } else {
            if ($decimals === null) {
                $decimals = 0;
            }
            $value *= 100;
            return number_format($value, $decimals, $this->decimalSeparator, $this->thousandSeparator) . '%';
        }
    }

    
    public function asScientific($value, $decimals = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);

        if ($this->_intlLoaded) {
            $f = $this->createNumberFormatter(NumberFormatter::SCIENTIFIC, $decimals, $options, $textOptions);
            if (($result = $f->format($value)) === false) {
                throw new InvalidParamException('Formatting scientific number value failed: ' . $f->getErrorCode() . ' ' . $f->getErrorMessage());
            }
            return $result;
        } else {
            if ($decimals !== null) {
                return sprintf("%.{$decimals}E", $value);
            } else {
                return sprintf('%.E', $value);
            }
        }
    }

    
    public function asCurrency($value, $currency = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);

        if ($this->_intlLoaded) {
            $currency = $currency ?: $this->currencyCode;
            // currency code must be set before fraction digits
            // http://php.net/manual/en/numberformatter.formatcurrency.php#114376
            if ($currency && !isset($textOptions[NumberFormatter::CURRENCY_CODE])) {
                $textOptions[NumberFormatter::CURRENCY_CODE] = $currency;
            }
            $formatter = $this->createNumberFormatter(NumberFormatter::CURRENCY, null, $options, $textOptions);
            if ($currency === null) {
                $result = $formatter->format($value);
            } else {
                $result = $formatter->formatCurrency($value, $currency);
            }
            if ($result === false) {
                throw new InvalidParamException('Formatting currency value failed: ' . $formatter->getErrorCode() . ' ' . $formatter->getErrorMessage());
            }
            return $result;
        } else {
            if ($currency === null) {
                if ($this->currencyCode === null) {
                    throw new InvalidConfigException('The default currency code for the formatter is not defined and the php intl extension is not installed which could take the default currency from the locale.');
                }
                $currency = $this->currencyCode;
            }
            return $currency . ' ' . $this->asDecimal($value, 2, $options, $textOptions);
        }
    }

    
    public function asSpellout($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);
        if ($this->_intlLoaded) {
            $f = $this->createNumberFormatter(NumberFormatter::SPELLOUT);
            if (($result = $f->format($value)) === false) {
                throw new InvalidParamException('Formatting number as spellout failed: ' . $f->getErrorCode() . ' ' . $f->getErrorMessage());
            }
            return $result;
        } else {
            throw new InvalidConfigException('Format as Spellout is only supported when PHP intl extension is installed.');
        }
    }

    
    public function asOrdinal($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);
        if ($this->_intlLoaded) {
            $f = $this->createNumberFormatter(NumberFormatter::ORDINAL);
            if (($result = $f->format($value)) === false) {
                throw new InvalidParamException('Formatting number as ordinal failed: ' . $f->getErrorCode() . ' ' . $f->getErrorMessage());
            }
            return $result;
        } else {
            throw new InvalidConfigException('Format as Ordinal is only supported when PHP intl extension is installed.');
        }
    }

    
    public function asShortSize($value, $decimals = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        list($params, $position) = $this->formatSizeNumber($value, $decimals, $options, $textOptions);

        if ($this->sizeFormatBase == 1024) {
            switch ($position) {
                case 0:
                    return Aabc::t('aabc', '{nFormatted} B', $params, $this->locale);
                case 1:
                    return Aabc::t('aabc', '{nFormatted} KiB', $params, $this->locale);
                case 2:
                    return Aabc::t('aabc', '{nFormatted} MiB', $params, $this->locale);
                case 3:
                    return Aabc::t('aabc', '{nFormatted} GiB', $params, $this->locale);
                case 4:
                    return Aabc::t('aabc', '{nFormatted} TiB', $params, $this->locale);
                default:
                    return Aabc::t('aabc', '{nFormatted} PiB', $params, $this->locale);
            }
        } else {
            switch ($position) {
                case 0:
                    return Aabc::t('aabc', '{nFormatted} B', $params, $this->locale);
                case 1:
                    return Aabc::t('aabc', '{nFormatted} KB', $params, $this->locale);
                case 2:
                    return Aabc::t('aabc', '{nFormatted} MB', $params, $this->locale);
                case 3:
                    return Aabc::t('aabc', '{nFormatted} GB', $params, $this->locale);
                case 4:
                    return Aabc::t('aabc', '{nFormatted} TB', $params, $this->locale);
                default:
                    return Aabc::t('aabc', '{nFormatted} PB', $params, $this->locale);
            }
        }
    }

    
    public function asSize($value, $decimals = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        list($params, $position) = $this->formatSizeNumber($value, $decimals, $options, $textOptions);

        if ($this->sizeFormatBase == 1024) {
            switch ($position) {
                case 0:
                    return Aabc::t('aabc', '{nFormatted} {n, plural, =1{byte} other{bytes}}', $params, $this->locale);
                case 1:
                    return Aabc::t('aabc', '{nFormatted} {n, plural, =1{kibibyte} other{kibibytes}}', $params, $this->locale);
                case 2:
                    return Aabc::t('aabc', '{nFormatted} {n, plural, =1{mebibyte} other{mebibytes}}', $params, $this->locale);
                case 3:
                    return Aabc::t('aabc', '{nFormatted} {n, plural, =1{gibibyte} other{gibibytes}}', $params, $this->locale);
                case 4:
                    return Aabc::t('aabc', '{nFormatted} {n, plural, =1{tebibyte} other{tebibytes}}', $params, $this->locale);
                default:
                    return Aabc::t('aabc', '{nFormatted} {n, plural, =1{pebibyte} other{pebibytes}}', $params, $this->locale);
            }
        } else {
            switch ($position) {
                case 0:
                    return Aabc::t('aabc', '{nFormatted} {n, plural, =1{byte} other{bytes}}', $params, $this->locale);
                case 1:
                    return Aabc::t('aabc', '{nFormatted} {n, plural, =1{kilobyte} other{kilobytes}}', $params, $this->locale);
                case 2:
                    return Aabc::t('aabc', '{nFormatted} {n, plural, =1{megabyte} other{megabytes}}', $params, $this->locale);
                case 3:
                    return Aabc::t('aabc', '{nFormatted} {n, plural, =1{gigabyte} other{gigabytes}}', $params, $this->locale);
                case 4:
                    return Aabc::t('aabc', '{nFormatted} {n, plural, =1{terabyte} other{terabytes}}', $params, $this->locale);
                default:
                    return Aabc::t('aabc', '{nFormatted} {n, plural, =1{petabyte} other{petabytes}}', $params, $this->locale);
            }
        }
    }


    
    private function formatSizeNumber($value, $decimals, $options, $textOptions)
    {
        $value = $this->normalizeNumericValue($value);

        $position = 0;
        do {
            if (abs($value) < $this->sizeFormatBase) {
                break;
            }
            $value /= $this->sizeFormatBase;
            $position++;
        } while ($position < 5);

        // no decimals for bytes
        if ($position === 0) {
            $decimals = 0;
        } elseif ($decimals !== null) {
            $value = round($value, $decimals);
        }
        // disable grouping for edge cases like 1023 to get 1023 B instead of 1,023 B
        $oldThousandSeparator = $this->thousandSeparator;
        $this->thousandSeparator = '';
        if ($this->_intlLoaded) {
            $options[NumberFormatter::GROUPING_USED] = false;
        }
        // format the size value
        $params = [
            // this is the unformatted number used for the plural rule
            // abs() to make sure the plural rules work correctly on negative numbers, intl does not cover this
            // http://english.stackexchange.com/questions/9735/is-1-singular-or-plural
            'n' => abs($value),
            // this is the formatted number used for display
            'nFormatted' => $this->asDecimal($value, $decimals, $options, $textOptions),
        ];
        $this->thousandSeparator = $oldThousandSeparator;

        return [$params, $position];
    }

    
    protected function normalizeNumericValue($value)
    {
        if (empty($value)) {
            return 0;
        }
        if (is_string($value) && is_numeric($value)) {
            $value = (float) $value;
        }
        if (!is_numeric($value)) {
            throw new InvalidParamException("'$value' is not a numeric value.");
        }
        return $value;
    }

    
    protected function createNumberFormatter($style, $decimals = null, $options = [], $textOptions = [])
    {
        $formatter = new NumberFormatter($this->locale, $style);

        // set text attributes
        foreach ($this->numberFormatterTextOptions as $name => $attribute) {
            $formatter->setTextAttribute($name, $attribute);
        }
        foreach ($textOptions as $name => $attribute) {
            $formatter->setTextAttribute($name, $attribute);
        }

        // set attributes
        foreach ($this->numberFormatterOptions as $name => $value) {
            $formatter->setAttribute($name, $value);
        }
        foreach ($options as $name => $value) {
            $formatter->setAttribute($name, $value);
        }
        if ($decimals !== null) {
            $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
            $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
        }

        // set symbols
        if ($this->decimalSeparator !== null) {
            $formatter->setSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, $this->decimalSeparator);
        }
        if ($this->thousandSeparator !== null) {
            $formatter->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, $this->thousandSeparator);
            $formatter->setSymbol(NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL, $this->thousandSeparator);
        }
        foreach ($this->numberFormatterSymbols as $name => $symbol) {
            $formatter->setSymbol($name, $symbol);
        }

        return $formatter;
    }
}
