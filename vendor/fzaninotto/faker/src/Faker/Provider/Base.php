<?php

namespace Faker\Provider;

use Faker\Generator;
use Faker\DefaultGenerator;
use Faker\UniqueGenerator;
use Faker\ValidGenerator;

class Base
{
    
    protected $generator;

    
    protected $unique;

    
    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    
    public static function randomDigit()
    {
        return mt_rand(0, 9);
    }

    
    public static function randomDigitNotNull()
    {
        return mt_rand(1, 9);
    }

    
    public static function randomDigitNot($except)
    {
        $result = self::numberBetween(0, 8);
        if ($result >= $except) {
            $result++;
        }
        return $result;
    }

    
    public static function randomNumber($nbDigits = null, $strict = false)
    {
        if (!is_bool($strict)) {
            throw new \InvalidArgumentException('randomNumber() generates numbers of fixed width. To generate numbers between two boundaries, use numberBetween() instead.');
        }
        if (null === $nbDigits) {
            $nbDigits = static::randomDigitNotNull();
        }
        $max = pow(10, $nbDigits) - 1;
        if ($max > mt_getrandmax()) {
            throw new \InvalidArgumentException('randomNumber() can only generate numbers up to mt_getrandmax()');
        }
        if ($strict) {
            return mt_rand(pow(10, $nbDigits - 1), $max);
        }

        return mt_rand(0, $max);
    }

    
    public static function randomFloat($nbMaxDecimals = null, $min = 0, $max = null)
    {
        if (null === $nbMaxDecimals) {
            $nbMaxDecimals = static::randomDigit();
        }

        if (null === $max) {
            $max = static::randomNumber();
        }

        if ($min > $max) {
            $tmp = $min;
            $min = $max;
            $max = $tmp;
        }

        return round($min + mt_rand() / mt_getrandmax() * ($max - $min), $nbMaxDecimals);
    }

    
    public static function numberBetween($int1 = 0, $int2 = 2147483647)
    {
        $min = $int1 < $int2 ? $int1 : $int2;
        $max = $int1 < $int2 ? $int2 : $int1;
        return mt_rand($min, $max);
    }

    
    public static function randomLetter()
    {
        return chr(mt_rand(97, 122));
    }

    
    public static function randomAscii()
    {
        return chr(mt_rand(33, 126));
    }

    
    public static function randomElements(array $array = array('a', 'b', 'c'), $count = 1)
    {
        $allKeys = array_keys($array);
        $numKeys = count($allKeys);

        if ($numKeys < $count) {
            throw new \LengthException(sprintf('Cannot get %d elements, only %d in array', $count, $numKeys));
        }

        $highKey = $numKeys - 1;
        $keys = $elements = array();
        $numElements = 0;

        while ($numElements < $count) {
            $num = mt_rand(0, $highKey);
            if (isset($keys[$num])) {
                continue;
            }

            $keys[$num] = true;
            $elements[] = $array[$allKeys[$num]];
            $numElements++;
        }

        return $elements;
    }

    
    public static function randomElement($array = array('a', 'b', 'c'))
    {
        if (!$array) {
            return null;
        }
        $elements = static::randomElements($array, 1);

        return $elements[0];
    }

    
    public static function randomKey($array = array())
    {
        if (!$array) {
            return null;
        }
        $keys = array_keys($array);
        $key = $keys[mt_rand(0, count($keys) - 1)];

        return $key;
    }

    
    public static function shuffle($arg = '')
    {
        if (is_array($arg)) {
            return static::shuffleArray($arg);
        }
        if (is_string($arg)) {
            return static::shuffleString($arg);
        }
        throw new \InvalidArgumentException('shuffle() only supports strings or arrays');
    }

    
    public static function shuffleArray($array = array())
    {
        $shuffledArray = array();
        $i = 0;
        reset($array);
        while (list($key, $value) = each($array)) {
            if ($i == 0) {
                $j = 0;
            } else {
                $j = mt_rand(0, $i);
            }
            if ($j == $i) {
                $shuffledArray[]= $value;
            } else {
                $shuffledArray[]= $shuffledArray[$j];
                $shuffledArray[$j] = $value;
            }
            $i++;
        }
        return $shuffledArray;
    }

    
    public static function shuffleString($string = '', $encoding = 'UTF-8')
    {
        if (function_exists('mb_strlen')) {
            // UTF8-safe str_split()
            $array = array();
            $strlen = mb_strlen($string, $encoding);
            for ($i = 0; $i < $strlen; $i++) {
                $array []= mb_substr($string, $i, 1, $encoding);
            }
        } else {
            $array = str_split($string, 1);
        }
        return implode('', static::shuffleArray($array));
    }

    private static function replaceWildcard($string, $wildcard = '#', $callback = 'static::randomDigit')
    {
        if (($pos = strpos($string, $wildcard)) === false) {
            return $string;
        }
        for ($i = $pos, $last = strrpos($string, $wildcard, $pos) + 1; $i < $last; $i++) {
            if ($string[$i] === $wildcard) {
                $string[$i] = call_user_func($callback);
            }
        }
        return $string;
    }

    
    public static function numerify($string = '###')
    {
        // instead of using randomDigit() several times, which is slow,
        // count the number of hashes and generate once a large number
        $toReplace = array();
        if (($pos = strpos($string, '#')) !== false) {
            for ($i = $pos, $last = strrpos($string, '#', $pos) + 1; $i < $last; $i++) {
                if ($string[$i] === '#') {
                    $toReplace[] = $i;
                }
            }
        }
        if ($nbReplacements = count($toReplace)) {
            $maxAtOnce = strlen((string) mt_getrandmax()) - 1;
            $numbers = '';
            $i = 0;
            while ($i < $nbReplacements) {
                $size = min($nbReplacements - $i, $maxAtOnce);
                $numbers .= str_pad(static::randomNumber($size), $size, '0', STR_PAD_LEFT);
                $i += $size;
            }
            for ($i = 0; $i < $nbReplacements; $i++) {
                $string[$toReplace[$i]] = $numbers[$i];
            }
        }
        $string = self::replaceWildcard($string, '%', 'static::randomDigitNotNull');

        return $string;
    }

    
    public static function lexify($string = '????')
    {
        return self::replaceWildcard($string, '?', 'static::randomLetter');
    }

    
    public static function bothify($string = '## ??')
    {
        $string = self::replaceWildcard($string, '*', function () {
            return mt_rand(0, 1) ? '#' : '?';
        });
        return static::lexify(static::numerify($string));
    }

    
    public static function asciify($string = '****')
    {
        return preg_replace_callback('/\*/u', 'static::randomAscii', $string);
    }

    
    public static function regexify($regex = '')
    {
        // ditch the anchors
        $regex = preg_replace('/^\/?\^?/', '', $regex);
        $regex = preg_replace('/\$?\/?$/', '', $regex);
        // All {2} become {2,2}
        $regex = preg_replace('/\{(\d+)\}/', '{\1,\1}', $regex);
        // Single-letter quantifiers (?, *, +) become bracket quantifiers ({0,1}, {0,rand}, {1, rand})
        $regex = preg_replace('/(?<!\\\)\?/', '{0,1}', $regex);
        $regex = preg_replace('/(?<!\\\)\*/', '{0,' . static::randomDigitNotNull() . '}', $regex);
        $regex = preg_replace('/(?<!\\\)\+/', '{1,' . static::randomDigitNotNull() . '}', $regex);
        // [12]{1,2} becomes [12] or [12][12]
        $regex = preg_replace_callback('/(\[[^\]]+\])\{(\d+),(\d+)\}/', function ($matches) {
            return str_repeat($matches[1], Base::randomElement(range($matches[2], $matches[3])));
        }, $regex);
        // (12|34){1,2} becomes (12|34) or (12|34)(12|34)
        $regex = preg_replace_callback('/(\([^\)]+\))\{(\d+),(\d+)\}/', function ($matches) {
            return str_repeat($matches[1], Base::randomElement(range($matches[2], $matches[3])));
        }, $regex);
        // A{1,2} becomes A or AA or \d{3} becomes \d\d\d
        $regex = preg_replace_callback('/(\\\?.)\{(\d+),(\d+)\}/', function ($matches) {
            return str_repeat($matches[1], Base::randomElement(range($matches[2], $matches[3])));
        }, $regex);
        // (this|that) becomes 'this' or 'that'
        $regex = preg_replace_callback('/\((.*?)\)/', function ($matches) {
            return Base::randomElement(explode('|', str_replace(array('(', ')'), '', $matches[1])));
        }, $regex);
        // All A-F inside of [] become ABCDEF
        $regex = preg_replace_callback('/\[([^\]]+)\]/', function ($matches) {
            return '[' . preg_replace_callback('/(\w|\d)\-(\w|\d)/', function ($range) {
                return implode(range($range[1], $range[2]), '');
            }, $matches[1]) . ']';
        }, $regex);
        // All [ABC] become B (or A or C)
        $regex = preg_replace_callback('/\[([^\]]+)\]/', function ($matches) {
            return Base::randomElement(str_split($matches[1]));
        }, $regex);
        // replace \d with number and \w with letter and . with ascii
        $regex = preg_replace_callback('/\\\w/', 'static::randomLetter', $regex);
        $regex = preg_replace_callback('/\\\d/', 'static::randomDigit', $regex);
        $regex = preg_replace_callback('/(?<!\\\)\./', 'static::randomAscii', $regex);
        // remove remaining backslashes
        $regex = str_replace('\\', '', $regex);
        // phew
        return $regex;
    }

    
    public static function toLower($string = '')
    {
        return extension_loaded('mbstring') ? mb_strtolower($string, 'UTF-8') : strtolower($string);
    }

    
    public static function toUpper($string = '')
    {
        return extension_loaded('mbstring') ? mb_strtoupper($string, 'UTF-8') : strtoupper($string);
    }

    
    public function optional($weight = 0.5, $default = null)
    {
        // old system based on 0.1 <= $weight <= 0.9
        // TODO: remove in v2
        if ($weight > 0 && $weight < 1 && mt_rand() / mt_getrandmax() <= $weight) {
            return $this->generator;
        }

        // new system with percentage
        if (is_int($weight) && mt_rand(1, 100) <= $weight) {
            return $this->generator;
        }

        return new DefaultGenerator($default);
    }

    
    public function unique($reset = false, $maxRetries = 10000)
    {
        if ($reset || !$this->unique) {
            $this->unique = new UniqueGenerator($this->generator, $maxRetries);
        }

        return $this->unique;
    }

    
    public function valid($validator = null, $maxRetries = 10000)
    {
        return new ValidGenerator($this->generator, $validator, $maxRetries);
    }
}
