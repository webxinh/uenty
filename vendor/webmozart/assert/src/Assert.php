<?php

/*
 * This file is part of the webmozart/assert package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Assert;

use BadMethodCallException;
use InvalidArgumentException;
use Traversable;
use Exception;
use Throwable;
use Closure;


class Assert
{
    public static function string($value, $message = '')
    {
        if (!is_string($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a string. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    public static function stringNotEmpty($value, $message = '')
    {
        static::string($value, $message);
        static::notEmpty($value, $message);
    }

    public static function integer($value, $message = '')
    {
        if (!is_int($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected an integer. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    public static function integerish($value, $message = '')
    {
        if (!is_numeric($value) || $value != (int) $value) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected an integerish value. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    public static function float($value, $message = '')
    {
        if (!is_float($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a float. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    public static function numeric($value, $message = '')
    {
        if (!is_numeric($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a numeric. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    public static function boolean($value, $message = '')
    {
        if (!is_bool($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a boolean. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    public static function scalar($value, $message = '')
    {
        if (!is_scalar($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a scalar. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    public static function object($value, $message = '')
    {
        if (!is_object($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected an object. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    public static function resource($value, $type = null, $message = '')
    {
        if (!is_resource($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a resource. Got: %s',
                static::typeToString($value)
            ));
        }

        if ($type && $type !== get_resource_type($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a resource of type %2$s. Got: %s',
                static::typeToString($value),
                $type
            ));
        }
    }

    public static function isCallable($value, $message = '')
    {
        if (!is_callable($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a callable. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    public static function isArray($value, $message = '')
    {
        if (!is_array($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected an array. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    public static function isTraversable($value, $message = '')
    {
        if (!is_array($value) && !($value instanceof Traversable)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a traversable. Got: %s',
                static::typeToString($value)
            ));
        }
    }

    public static function isInstanceOf($value, $class, $message = '')
    {
        if (!($value instanceof $class)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected an instance of %2$s. Got: %s',
                static::typeToString($value),
                $class
            ));
        }
    }

    public static function notInstanceOf($value, $class, $message = '')
    {
        if ($value instanceof $class) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected an instance other than %2$s. Got: %s',
                static::typeToString($value),
                $class
            ));
        }
    }

    public static function isEmpty($value, $message = '')
    {
        if (!empty($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected an empty value. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    public static function notEmpty($value, $message = '')
    {
        if (empty($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a non-empty value. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    public static function null($value, $message = '')
    {
        if (null !== $value) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected null. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    public static function notNull($value, $message = '')
    {
        if (null === $value) {
            static::reportInvalidArgument(
                $message ?: 'Expected a value other than null.'
            );
        }
    }

    public static function true($value, $message = '')
    {
        if (true !== $value) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value to be true. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    public static function false($value, $message = '')
    {
        if (false !== $value) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value to be false. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    public static function eq($value, $value2, $message = '')
    {
        if ($value2 != $value) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value equal to %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($value2)
            ));
        }
    }

    public static function notEq($value, $value2, $message = '')
    {
        if ($value2 == $value) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a different value than %s.',
                static::valueToString($value2)
            ));
        }
    }

    public static function same($value, $value2, $message = '')
    {
        if ($value2 !== $value) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value identical to %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($value2)
            ));
        }
    }

    public static function notSame($value, $value2, $message = '')
    {
        if ($value2 === $value) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value not identical to %s.',
                static::valueToString($value2)
            ));
        }
    }

    public static function greaterThan($value, $limit, $message = '')
    {
        if ($value <= $limit) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value greater than %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($limit)
            ));
        }
    }

    public static function greaterThanEq($value, $limit, $message = '')
    {
        if ($value < $limit) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value greater than or equal to %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($limit)
            ));
        }
    }

    public static function lessThan($value, $limit, $message = '')
    {
        if ($value >= $limit) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value less than %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($limit)
            ));
        }
    }

    public static function lessThanEq($value, $limit, $message = '')
    {
        if ($value > $limit) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value less than or equal to %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($limit)
            ));
        }
    }

    public static function range($value, $min, $max, $message = '')
    {
        if ($value < $min || $value > $max) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value between %2$s and %3$s. Got: %s',
                static::valueToString($value),
                static::valueToString($min),
                static::valueToString($max)
            ));
        }
    }

    public static function oneOf($value, array $values, $message = '')
    {
        if (!in_array($value, $values, true)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected one of: %2$s. Got: %s',
                static::valueToString($value),
                implode(', ', array_map(array('static', 'valueToString'), $values))
            ));
        }
    }

    public static function contains($value, $subString, $message = '')
    {
        if (false === strpos($value, $subString)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value to contain %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($subString)
            ));
        }
    }

    public static function startsWith($value, $prefix, $message = '')
    {
        if (0 !== strpos($value, $prefix)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value to start with %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($prefix)
            ));
        }
    }

    public static function startsWithLetter($value, $message = '')
    {
        $valid = isset($value[0]);

        if ($valid) {
            $locale = setlocale(LC_CTYPE, 0);
            setlocale(LC_CTYPE, 'C');
            $valid = ctype_alpha($value[0]);
            setlocale(LC_CTYPE, $locale);
        }

        if (!$valid) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value to start with a letter. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    public static function endsWith($value, $suffix, $message = '')
    {
        if ($suffix !== substr($value, -static::strlen($suffix))) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value to end with %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($suffix)
            ));
        }
    }

    public static function regex($value, $pattern, $message = '')
    {
        if (!preg_match($pattern, $value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'The value %s does not match the expected pattern.',
                static::valueToString($value)
            ));
        }
    }

    public static function alpha($value, $message = '')
    {
        $locale = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, 'C');
        $valid = !ctype_alpha($value);
        setlocale(LC_CTYPE, $locale);

        if ($valid) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value to contain only letters. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    public static function digits($value, $message = '')
    {
        $locale = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, 'C');
        $valid = !ctype_digit($value);
        setlocale(LC_CTYPE, $locale);

        if ($valid) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value to contain digits only. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    public static function alnum($value, $message = '')
    {
        $locale = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, 'C');
        $valid = !ctype_alnum($value);
        setlocale(LC_CTYPE, $locale);

        if ($valid) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value to contain letters and digits only. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    public static function lower($value, $message = '')
    {
        $locale = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, 'C');
        $valid = !ctype_lower($value);
        setlocale(LC_CTYPE, $locale);

        if ($valid) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value to contain lowercase characters only. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    public static function upper($value, $message = '')
    {
        $locale = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, 'C');
        $valid = !ctype_upper($value);
        setlocale(LC_CTYPE, $locale);

        if ($valid) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value to contain uppercase characters only. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    public static function length($value, $length, $message = '')
    {
        if ($length !== static::strlen($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value to contain %2$s characters. Got: %s',
                static::valueToString($value),
                $length
            ));
        }
    }

    public static function minLength($value, $min, $message = '')
    {
        if (static::strlen($value) < $min) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value to contain at least %2$s characters. Got: %s',
                static::valueToString($value),
                $min
            ));
        }
    }

    public static function maxLength($value, $max, $message = '')
    {
        if (static::strlen($value) > $max) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value to contain at most %2$s characters. Got: %s',
                static::valueToString($value),
                $max
            ));
        }
    }

    public static function lengthBetween($value, $min, $max, $message = '')
    {
        $length = static::strlen($value);

        if ($length < $min || $length > $max) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a value to contain between %2$s and %3$s characters. Got: %s',
                static::valueToString($value),
                $min,
                $max
            ));
        }
    }

    public static function fileExists($value, $message = '')
    {
        static::string($value);

        if (!file_exists($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'The file %s does not exist.',
                static::valueToString($value)
            ));
        }
    }

    public static function file($value, $message = '')
    {
        static::fileExists($value, $message);

        if (!is_file($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'The path %s is not a file.',
                static::valueToString($value)
            ));
        }
    }

    public static function directory($value, $message = '')
    {
        static::fileExists($value, $message);

        if (!is_dir($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'The path %s is no directory.',
                static::valueToString($value)
            ));
        }
    }

    public static function readable($value, $message = '')
    {
        if (!is_readable($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'The path %s is not readable.',
                static::valueToString($value)
            ));
        }
    }

    public static function writable($value, $message = '')
    {
        if (!is_writable($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'The path %s is not writable.',
                static::valueToString($value)
            ));
        }
    }

    public static function classExists($value, $message = '')
    {
        if (!class_exists($value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected an existing class name. Got: %s',
                static::valueToString($value)
            ));
        }
    }

    public static function subclassOf($value, $class, $message = '')
    {
        if (!is_subclass_of($value, $class)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected a sub-class of %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($class)
            ));
        }
    }

    public static function implementsInterface($value, $interface, $message = '')
    {
        if (!in_array($interface, class_implements($value))) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected an implementation of %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($interface)
            ));
        }
    }

    public static function propertyExists($classOrObject, $property, $message = '')
    {
        if (!property_exists($classOrObject, $property)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected the property %s to exist.',
                static::valueToString($property)
            ));
        }
    }

    public static function propertyNotExists($classOrObject, $property, $message = '')
    {
        if (property_exists($classOrObject, $property)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected the property %s to not exist.',
                static::valueToString($property)
            ));
        }
    }

    public static function methodExists($classOrObject, $method, $message = '')
    {
        if (!method_exists($classOrObject, $method)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected the method %s to exist.',
                static::valueToString($method)
            ));
        }
    }

    public static function methodNotExists($classOrObject, $method, $message = '')
    {
        if (method_exists($classOrObject, $method)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected the method %s to not exist.',
                static::valueToString($method)
            ));
        }
    }

    public static function keyExists($array, $key, $message = '')
    {
        if (!array_key_exists($key, $array)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected the key %s to exist.',
                static::valueToString($key)
            ));
        }
    }

    public static function keyNotExists($array, $key, $message = '')
    {
        if (array_key_exists($key, $array)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Expected the key %s to not exist.',
                static::valueToString($key)
            ));
        }
    }

    public static function count($array, $number, $message = '')
    {
        static::eq(
            count($array),
            $number,
            $message ?: sprintf('Expected an array to contain %d elements. Got: %d.', $number, count($array))
        );
    }

    public static function uuid($value, $message = '')
    {
        $value = str_replace(array('urn:', 'uuid:', '{', '}'), '', $value);

        // The nil UUID is special form of UUID that is specified to have all
        // 128 bits set to zero.
        if ('00000000-0000-0000-0000-000000000000' === $value) {
            return;
        }

        if (!preg_match('/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/', $value)) {
            static::reportInvalidArgument(sprintf(
                $message ?: 'Value %s is not a valid UUID.',
                static::valueToString($value)
            ));
        }
    }

    public static function throws(Closure $expression, $class = 'Exception', $message = '')
    {
        static::string($class);

        $actual = 'none';
        try {
            $expression();
        } catch (Exception $e) {
            $actual = get_class($e);
            if ($e instanceof $class) {
                return;
            }
        } catch (Throwable $e) {
            $actual = get_class($e);
            if ($e instanceof $class) {
                return;
            }
        }

        static::reportInvalidArgument($message ?: sprintf(
            'Expected to throw "%s", got "%s"',
            $class,
            $actual
        ));
    }

    public static function __callStatic($name, $arguments)
    {
        if ('nullOr' === substr($name, 0, 6)) {
            if (null !== $arguments[0]) {
                $method = lcfirst(substr($name, 6));
                call_user_func_array(array('static', $method), $arguments);
            }

            return;
        }

        if ('all' === substr($name, 0, 3)) {
            static::isTraversable($arguments[0]);

            $method = lcfirst(substr($name, 3));
            $args = $arguments;

            foreach ($arguments[0] as $entry) {
                $args[0] = $entry;

                call_user_func_array(array('static', $method), $args);
            }

            return;
        }

        throw new BadMethodCallException('No such method: '.$name);
    }

    protected static function valueToString($value)
    {
        if (null === $value) {
            return 'null';
        }

        if (true === $value) {
            return 'true';
        }

        if (false === $value) {
            return 'false';
        }

        if (is_array($value)) {
            return 'array';
        }

        if (is_object($value)) {
            return get_class($value);
        }

        if (is_resource($value)) {
            return 'resource';
        }

        if (is_string($value)) {
            return '"'.$value.'"';
        }

        return (string) $value;
    }

    protected static function typeToString($value)
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }

    protected static function strlen($value)
    {
        if (!function_exists('mb_detect_encoding')) {
            return strlen($value);
        }

        if (false === $encoding = mb_detect_encoding($value)) {
            return strlen($value);
        }

        return mb_strwidth($value, $encoding);
    }

    protected static function reportInvalidArgument($message)
    {
        throw new InvalidArgumentException($message);
    }

    private function __construct()
    {
    }
}
