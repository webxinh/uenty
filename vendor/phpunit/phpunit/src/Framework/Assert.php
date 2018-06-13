<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


abstract class PHPUnit_Framework_Assert
{
    
    private static $count = 0;

    
    public static function assertArrayHasKey($key, $array, $message = '')
    {
        if (!(is_integer($key) || is_string($key))) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
                1,
                'integer or string'
            );
        }

        if (!(is_array($array) || $array instanceof ArrayAccess)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
                2,
                'array or ArrayAccess'
            );
        }

        $constraint = new PHPUnit_Framework_Constraint_ArrayHasKey($key);

        static::assertThat($array, $constraint, $message);
    }

    
    public static function assertArraySubset($subset, $array, $strict = false, $message = '')
    {
        if (!(is_array($subset) || $subset instanceof ArrayAccess)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
                1,
                'array or ArrayAccess'
            );
        }

        if (!(is_array($array) || $array instanceof ArrayAccess)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
                2,
                'array or ArrayAccess'
            );
        }

        $constraint = new PHPUnit_Framework_Constraint_ArraySubset($subset, $strict);

        static::assertThat($array, $constraint, $message);
    }

    
    public static function assertArrayNotHasKey($key, $array, $message = '')
    {
        if (!(is_integer($key) || is_string($key))) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
                1,
                'integer or string'
            );
        }

        if (!(is_array($array) || $array instanceof ArrayAccess)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
                2,
                'array or ArrayAccess'
            );
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_ArrayHasKey($key)
        );

        static::assertThat($array, $constraint, $message);
    }

    
    public static function assertContains($needle, $haystack, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
    {
        if (is_array($haystack) ||
            is_object($haystack) && $haystack instanceof Traversable) {
            $constraint = new PHPUnit_Framework_Constraint_TraversableContains(
                $needle,
                $checkForObjectIdentity,
                $checkForNonObjectIdentity
            );
        } elseif (is_string($haystack)) {
            if (!is_string($needle)) {
                throw PHPUnit_Util_InvalidArgumentHelper::factory(
                    1,
                    'string'
                );
            }

            $constraint = new PHPUnit_Framework_Constraint_StringContains(
                $needle,
                $ignoreCase
            );
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
                2,
                'array, traversable or string'
            );
        }

        static::assertThat($haystack, $constraint, $message);
    }

    
    public static function assertAttributeContains($needle, $haystackAttributeName, $haystackClassOrObject, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
    {
        static::assertContains(
            $needle,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message,
            $ignoreCase,
            $checkForObjectIdentity,
            $checkForNonObjectIdentity
        );
    }

    
    public static function assertNotContains($needle, $haystack, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
    {
        if (is_array($haystack) ||
            is_object($haystack) && $haystack instanceof Traversable) {
            $constraint = new PHPUnit_Framework_Constraint_Not(
                new PHPUnit_Framework_Constraint_TraversableContains(
                    $needle,
                    $checkForObjectIdentity,
                    $checkForNonObjectIdentity
                )
            );
        } elseif (is_string($haystack)) {
            if (!is_string($needle)) {
                throw PHPUnit_Util_InvalidArgumentHelper::factory(
                    1,
                    'string'
                );
            }

            $constraint = new PHPUnit_Framework_Constraint_Not(
                new PHPUnit_Framework_Constraint_StringContains(
                    $needle,
                    $ignoreCase
                )
            );
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
                2,
                'array, traversable or string'
            );
        }

        static::assertThat($haystack, $constraint, $message);
    }

    
    public static function assertAttributeNotContains($needle, $haystackAttributeName, $haystackClassOrObject, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
    {
        static::assertNotContains(
            $needle,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message,
            $ignoreCase,
            $checkForObjectIdentity,
            $checkForNonObjectIdentity
        );
    }

    
    public static function assertContainsOnly($type, $haystack, $isNativeType = null, $message = '')
    {
        if (!(is_array($haystack) ||
            is_object($haystack) && $haystack instanceof Traversable)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
                2,
                'array or traversable'
            );
        }

        if ($isNativeType == null) {
            $isNativeType = PHPUnit_Util_Type::isType($type);
        }

        static::assertThat(
            $haystack,
            new PHPUnit_Framework_Constraint_TraversableContainsOnly(
                $type,
                $isNativeType
            ),
            $message
        );
    }

    
    public static function assertContainsOnlyInstancesOf($classname, $haystack, $message = '')
    {
        if (!(is_array($haystack) ||
            is_object($haystack) && $haystack instanceof Traversable)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
                2,
                'array or traversable'
            );
        }

        static::assertThat(
            $haystack,
            new PHPUnit_Framework_Constraint_TraversableContainsOnly(
                $classname,
                false
            ),
            $message
        );
    }

    
    public static function assertAttributeContainsOnly($type, $haystackAttributeName, $haystackClassOrObject, $isNativeType = null, $message = '')
    {
        static::assertContainsOnly(
            $type,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $isNativeType,
            $message
        );
    }

    
    public static function assertNotContainsOnly($type, $haystack, $isNativeType = null, $message = '')
    {
        if (!(is_array($haystack) ||
            is_object($haystack) && $haystack instanceof Traversable)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
                2,
                'array or traversable'
            );
        }

        if ($isNativeType == null) {
            $isNativeType = PHPUnit_Util_Type::isType($type);
        }

        static::assertThat(
            $haystack,
            new PHPUnit_Framework_Constraint_Not(
                new PHPUnit_Framework_Constraint_TraversableContainsOnly(
                    $type,
                    $isNativeType
                )
            ),
            $message
        );
    }

    
    public static function assertAttributeNotContainsOnly($type, $haystackAttributeName, $haystackClassOrObject, $isNativeType = null, $message = '')
    {
        static::assertNotContainsOnly(
            $type,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $isNativeType,
            $message
        );
    }

    
    public static function assertCount($expectedCount, $haystack, $message = '')
    {
        if (!is_int($expectedCount)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'integer');
        }

        if (!$haystack instanceof Countable &&
            !$haystack instanceof Traversable &&
            !is_array($haystack)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'countable or traversable');
        }

        static::assertThat(
            $haystack,
            new PHPUnit_Framework_Constraint_Count($expectedCount),
            $message
        );
    }

    
    public static function assertAttributeCount($expectedCount, $haystackAttributeName, $haystackClassOrObject, $message = '')
    {
        static::assertCount(
            $expectedCount,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message
        );
    }

    
    public static function assertNotCount($expectedCount, $haystack, $message = '')
    {
        if (!is_int($expectedCount)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'integer');
        }

        if (!$haystack instanceof Countable &&
            !$haystack instanceof Traversable &&
            !is_array($haystack)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'countable or traversable');
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_Count($expectedCount)
        );

        static::assertThat($haystack, $constraint, $message);
    }

    
    public static function assertAttributeNotCount($expectedCount, $haystackAttributeName, $haystackClassOrObject, $message = '')
    {
        static::assertNotCount(
            $expectedCount,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message
        );
    }

    
    public static function assertEquals($expected, $actual, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        $constraint = new PHPUnit_Framework_Constraint_IsEqual(
            $expected,
            $delta,
            $maxDepth,
            $canonicalize,
            $ignoreCase
        );

        static::assertThat($actual, $constraint, $message);
    }

    
    public static function assertAttributeEquals($expected, $actualAttributeName, $actualClassOrObject, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        static::assertEquals(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message,
            $delta,
            $maxDepth,
            $canonicalize,
            $ignoreCase
        );
    }

    
    public static function assertNotEquals($expected, $actual, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        $constraint = new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_IsEqual(
                $expected,
                $delta,
                $maxDepth,
                $canonicalize,
                $ignoreCase
            )
        );

        static::assertThat($actual, $constraint, $message);
    }

    
    public static function assertAttributeNotEquals($expected, $actualAttributeName, $actualClassOrObject, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        static::assertNotEquals(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message,
            $delta,
            $maxDepth,
            $canonicalize,
            $ignoreCase
        );
    }

    
    public static function assertEmpty($actual, $message = '')
    {
        static::assertThat($actual, static::isEmpty(), $message);
    }

    
    public static function assertAttributeEmpty($haystackAttributeName, $haystackClassOrObject, $message = '')
    {
        static::assertEmpty(
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message
        );
    }

    
    public static function assertNotEmpty($actual, $message = '')
    {
        static::assertThat($actual, static::logicalNot(static::isEmpty()), $message);
    }

    
    public static function assertAttributeNotEmpty($haystackAttributeName, $haystackClassOrObject, $message = '')
    {
        static::assertNotEmpty(
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message
        );
    }

    
    public static function assertGreaterThan($expected, $actual, $message = '')
    {
        static::assertThat($actual, static::greaterThan($expected), $message);
    }

    
    public static function assertAttributeGreaterThan($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        static::assertGreaterThan(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
    }

    
    public static function assertGreaterThanOrEqual($expected, $actual, $message = '')
    {
        static::assertThat(
            $actual,
            static::greaterThanOrEqual($expected),
            $message
        );
    }

    
    public static function assertAttributeGreaterThanOrEqual($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        static::assertGreaterThanOrEqual(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
    }

    
    public static function assertLessThan($expected, $actual, $message = '')
    {
        static::assertThat($actual, static::lessThan($expected), $message);
    }

    
    public static function assertAttributeLessThan($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        static::assertLessThan(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
    }

    
    public static function assertLessThanOrEqual($expected, $actual, $message = '')
    {
        static::assertThat($actual, static::lessThanOrEqual($expected), $message);
    }

    
    public static function assertAttributeLessThanOrEqual($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        static::assertLessThanOrEqual(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
    }

    
    public static function assertFileEquals($expected, $actual, $message = '', $canonicalize = false, $ignoreCase = false)
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);

        static::assertEquals(
            file_get_contents($expected),
            file_get_contents($actual),
            $message,
            0,
            10,
            $canonicalize,
            $ignoreCase
        );
    }

    
    public static function assertFileNotEquals($expected, $actual, $message = '', $canonicalize = false, $ignoreCase = false)
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);

        static::assertNotEquals(
            file_get_contents($expected),
            file_get_contents($actual),
            $message,
            0,
            10,
            $canonicalize,
            $ignoreCase
        );
    }

    
    public static function assertStringEqualsFile($expectedFile, $actualString, $message = '', $canonicalize = false, $ignoreCase = false)
    {
        static::assertFileExists($expectedFile, $message);

        static::assertEquals(
            file_get_contents($expectedFile),
            $actualString,
            $message,
            0,
            10,
            $canonicalize,
            $ignoreCase
        );
    }

    
    public static function assertStringNotEqualsFile($expectedFile, $actualString, $message = '', $canonicalize = false, $ignoreCase = false)
    {
        static::assertFileExists($expectedFile, $message);

        static::assertNotEquals(
            file_get_contents($expectedFile),
            $actualString,
            $message,
            0,
            10,
            $canonicalize,
            $ignoreCase
        );
    }

    
    public static function assertIsReadable($filename, $message = '')
    {
        if (!is_string($filename)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_IsReadable;

        static::assertThat($filename, $constraint, $message);
    }

    
    public static function assertNotIsReadable($filename, $message = '')
    {
        if (!is_string($filename)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_IsReadable
        );

        static::assertThat($filename, $constraint, $message);
    }

    
    public static function assertIsWritable($filename, $message = '')
    {
        if (!is_string($filename)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_IsWritable;

        static::assertThat($filename, $constraint, $message);
    }

    
    public static function assertNotIsWritable($filename, $message = '')
    {
        if (!is_string($filename)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_IsWritable
        );

        static::assertThat($filename, $constraint, $message);
    }

    
    public static function assertDirectoryExists($directory, $message = '')
    {
        if (!is_string($directory)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_DirectoryExists;

        static::assertThat($directory, $constraint, $message);
    }

    
    public static function assertDirectoryNotExists($directory, $message = '')
    {
        if (!is_string($directory)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_DirectoryExists
        );

        static::assertThat($directory, $constraint, $message);
    }

    
    public static function assertDirectoryIsReadable($directory, $message = '')
    {
        self::assertDirectoryExists($directory, $message);
        self::assertIsReadable($directory, $message);
    }

    
    public static function assertDirectoryNotIsReadable($directory, $message = '')
    {
        self::assertDirectoryExists($directory, $message);
        self::assertNotIsReadable($directory, $message);
    }

    
    public static function assertDirectoryIsWritable($directory, $message = '')
    {
        self::assertDirectoryExists($directory, $message);
        self::assertIsWritable($directory, $message);
    }

    
    public static function assertDirectoryNotIsWritable($directory, $message = '')
    {
        self::assertDirectoryExists($directory, $message);
        self::assertNotIsWritable($directory, $message);
    }

    
    public static function assertFileExists($filename, $message = '')
    {
        if (!is_string($filename)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_FileExists;

        static::assertThat($filename, $constraint, $message);
    }

    
    public static function assertFileNotExists($filename, $message = '')
    {
        if (!is_string($filename)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_FileExists
        );

        static::assertThat($filename, $constraint, $message);
    }

    
    public static function assertFileIsReadable($file, $message = '')
    {
        self::assertFileExists($file, $message);
        self::assertIsReadable($file, $message);
    }

    
    public static function assertFileNotIsReadable($file, $message = '')
    {
        self::assertFileExists($file, $message);
        self::assertNotIsReadable($file, $message);
    }

    
    public static function assertFileIsWritable($file, $message = '')
    {
        self::assertFileExists($file, $message);
        self::assertIsWritable($file, $message);
    }

    
    public static function assertFileNotIsWritable($file, $message = '')
    {
        self::assertFileExists($file, $message);
        self::assertNotIsWritable($file, $message);
    }

    
    public static function assertTrue($condition, $message = '')
    {
        static::assertThat($condition, static::isTrue(), $message);
    }

    
    public static function assertNotTrue($condition, $message = '')
    {
        static::assertThat($condition, static::logicalNot(static::isTrue()), $message);
    }

    
    public static function assertFalse($condition, $message = '')
    {
        static::assertThat($condition, static::isFalse(), $message);
    }

    
    public static function assertNotFalse($condition, $message = '')
    {
        static::assertThat($condition, static::logicalNot(static::isFalse()), $message);
    }

    
    public static function assertNull($actual, $message = '')
    {
        static::assertThat($actual, static::isNull(), $message);
    }

    
    public static function assertNotNull($actual, $message = '')
    {
        static::assertThat($actual, static::logicalNot(static::isNull()), $message);
    }

    
    public static function assertFinite($actual, $message = '')
    {
        static::assertThat($actual, static::isFinite(), $message);
    }

    
    public static function assertInfinite($actual, $message = '')
    {
        static::assertThat($actual, static::isInfinite(), $message);
    }

    
    public static function assertNan($actual, $message = '')
    {
        static::assertThat($actual, static::isNan(), $message);
    }

    
    public static function assertClassHasAttribute($attributeName, $className, $message = '')
    {
        if (!is_string($attributeName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'valid attribute name');
        }

        if (!is_string($className) || !class_exists($className)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'class name', $className);
        }

        $constraint = new PHPUnit_Framework_Constraint_ClassHasAttribute(
            $attributeName
        );

        static::assertThat($className, $constraint, $message);
    }

    
    public static function assertClassNotHasAttribute($attributeName, $className, $message = '')
    {
        if (!is_string($attributeName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'valid attribute name');
        }

        if (!is_string($className) || !class_exists($className)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'class name', $className);
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_ClassHasAttribute($attributeName)
        );

        static::assertThat($className, $constraint, $message);
    }

    
    public static function assertClassHasStaticAttribute($attributeName, $className, $message = '')
    {
        if (!is_string($attributeName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'valid attribute name');
        }

        if (!is_string($className) || !class_exists($className)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'class name', $className);
        }

        $constraint = new PHPUnit_Framework_Constraint_ClassHasStaticAttribute(
            $attributeName
        );

        static::assertThat($className, $constraint, $message);
    }

    
    public static function assertClassNotHasStaticAttribute($attributeName, $className, $message = '')
    {
        if (!is_string($attributeName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'valid attribute name');
        }

        if (!is_string($className) || !class_exists($className)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'class name', $className);
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_ClassHasStaticAttribute(
                $attributeName
            )
        );

        static::assertThat($className, $constraint, $message);
    }

    
    public static function assertObjectHasAttribute($attributeName, $object, $message = '')
    {
        if (!is_string($attributeName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'valid attribute name');
        }

        if (!is_object($object)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'object');
        }

        $constraint = new PHPUnit_Framework_Constraint_ObjectHasAttribute(
            $attributeName
        );

        static::assertThat($object, $constraint, $message);
    }

    
    public static function assertObjectNotHasAttribute($attributeName, $object, $message = '')
    {
        if (!is_string($attributeName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'valid attribute name');
        }

        if (!is_object($object)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'object');
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_ObjectHasAttribute($attributeName)
        );

        static::assertThat($object, $constraint, $message);
    }

    
    public static function assertSame($expected, $actual, $message = '')
    {
        if (is_bool($expected) && is_bool($actual)) {
            static::assertEquals($expected, $actual, $message);
        } else {
            $constraint = new PHPUnit_Framework_Constraint_IsIdentical(
                $expected
            );

            static::assertThat($actual, $constraint, $message);
        }
    }

    
    public static function assertAttributeSame($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        static::assertSame(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
    }

    
    public static function assertNotSame($expected, $actual, $message = '')
    {
        if (is_bool($expected) && is_bool($actual)) {
            static::assertNotEquals($expected, $actual, $message);
        } else {
            $constraint = new PHPUnit_Framework_Constraint_Not(
                new PHPUnit_Framework_Constraint_IsIdentical($expected)
            );

            static::assertThat($actual, $constraint, $message);
        }
    }

    
    public static function assertAttributeNotSame($expected, $actualAttributeName, $actualClassOrObject, $message = '')
    {
        static::assertNotSame(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
    }

    
    public static function assertInstanceOf($expected, $actual, $message = '')
    {
        if (!(is_string($expected) && (class_exists($expected) || interface_exists($expected)))) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'class or interface name');
        }

        $constraint = new PHPUnit_Framework_Constraint_IsInstanceOf(
            $expected
        );

        static::assertThat($actual, $constraint, $message);
    }

    
    public static function assertAttributeInstanceOf($expected, $attributeName, $classOrObject, $message = '')
    {
        static::assertInstanceOf(
            $expected,
            static::readAttribute($classOrObject, $attributeName),
            $message
        );
    }

    
    public static function assertNotInstanceOf($expected, $actual, $message = '')
    {
        if (!(is_string($expected) && (class_exists($expected) || interface_exists($expected)))) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'class or interface name');
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_IsInstanceOf($expected)
        );

        static::assertThat($actual, $constraint, $message);
    }

    
    public static function assertAttributeNotInstanceOf($expected, $attributeName, $classOrObject, $message = '')
    {
        static::assertNotInstanceOf(
            $expected,
            static::readAttribute($classOrObject, $attributeName),
            $message
        );
    }

    
    public static function assertInternalType($expected, $actual, $message = '')
    {
        if (!is_string($expected)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_IsType(
            $expected
        );

        static::assertThat($actual, $constraint, $message);
    }

    
    public static function assertAttributeInternalType($expected, $attributeName, $classOrObject, $message = '')
    {
        static::assertInternalType(
            $expected,
            static::readAttribute($classOrObject, $attributeName),
            $message
        );
    }

    
    public static function assertNotInternalType($expected, $actual, $message = '')
    {
        if (!is_string($expected)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_IsType($expected)
        );

        static::assertThat($actual, $constraint, $message);
    }

    
    public static function assertAttributeNotInternalType($expected, $attributeName, $classOrObject, $message = '')
    {
        static::assertNotInternalType(
            $expected,
            static::readAttribute($classOrObject, $attributeName),
            $message
        );
    }

    
    public static function assertRegExp($pattern, $string, $message = '')
    {
        if (!is_string($pattern)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!is_string($string)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_PCREMatch($pattern);

        static::assertThat($string, $constraint, $message);
    }

    
    public static function assertNotRegExp($pattern, $string, $message = '')
    {
        if (!is_string($pattern)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!is_string($string)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_PCREMatch($pattern)
        );

        static::assertThat($string, $constraint, $message);
    }

    
    public static function assertSameSize($expected, $actual, $message = '')
    {
        if (!$expected instanceof Countable &&
            !$expected instanceof Traversable &&
            !is_array($expected)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'countable or traversable');
        }

        if (!$actual instanceof Countable &&
            !$actual instanceof Traversable &&
            !is_array($actual)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'countable or traversable');
        }

        static::assertThat(
            $actual,
            new PHPUnit_Framework_Constraint_SameSize($expected),
            $message
        );
    }

    
    public static function assertNotSameSize($expected, $actual, $message = '')
    {
        if (!$expected instanceof Countable &&
            !$expected instanceof Traversable &&
            !is_array($expected)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'countable or traversable');
        }

        if (!$actual instanceof Countable &&
            !$actual instanceof Traversable &&
            !is_array($actual)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'countable or traversable');
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_SameSize($expected)
        );

        static::assertThat($actual, $constraint, $message);
    }

    
    public static function assertStringMatchesFormat($format, $string, $message = '')
    {
        if (!is_string($format)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!is_string($string)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_StringMatches($format);

        static::assertThat($string, $constraint, $message);
    }

    
    public static function assertStringNotMatchesFormat($format, $string, $message = '')
    {
        if (!is_string($format)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!is_string($string)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_StringMatches($format)
        );

        static::assertThat($string, $constraint, $message);
    }

    
    public static function assertStringMatchesFormatFile($formatFile, $string, $message = '')
    {
        static::assertFileExists($formatFile, $message);

        if (!is_string($string)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_StringMatches(
            file_get_contents($formatFile)
        );

        static::assertThat($string, $constraint, $message);
    }

    
    public static function assertStringNotMatchesFormatFile($formatFile, $string, $message = '')
    {
        static::assertFileExists($formatFile, $message);

        if (!is_string($string)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_StringMatches(
                file_get_contents($formatFile)
            )
        );

        static::assertThat($string, $constraint, $message);
    }

    
    public static function assertStringStartsWith($prefix, $string, $message = '')
    {
        if (!is_string($prefix)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!is_string($string)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_StringStartsWith(
            $prefix
        );

        static::assertThat($string, $constraint, $message);
    }

    
    public static function assertStringStartsNotWith($prefix, $string, $message = '')
    {
        if (!is_string($prefix)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!is_string($string)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_StringStartsWith($prefix)
        );

        static::assertThat($string, $constraint, $message);
    }

    
    public static function assertStringEndsWith($suffix, $string, $message = '')
    {
        if (!is_string($suffix)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!is_string($string)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_StringEndsWith($suffix);

        static::assertThat($string, $constraint, $message);
    }

    
    public static function assertStringEndsNotWith($suffix, $string, $message = '')
    {
        if (!is_string($suffix)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!is_string($string)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'string');
        }

        $constraint = new PHPUnit_Framework_Constraint_Not(
            new PHPUnit_Framework_Constraint_StringEndsWith($suffix)
        );

        static::assertThat($string, $constraint, $message);
    }

    
    public static function assertXmlFileEqualsXmlFile($expectedFile, $actualFile, $message = '')
    {
        $expected = PHPUnit_Util_XML::loadFile($expectedFile);
        $actual   = PHPUnit_Util_XML::loadFile($actualFile);

        static::assertEquals($expected, $actual, $message);
    }

    
    public static function assertXmlFileNotEqualsXmlFile($expectedFile, $actualFile, $message = '')
    {
        $expected = PHPUnit_Util_XML::loadFile($expectedFile);
        $actual   = PHPUnit_Util_XML::loadFile($actualFile);

        static::assertNotEquals($expected, $actual, $message);
    }

    
    public static function assertXmlStringEqualsXmlFile($expectedFile, $actualXml, $message = '')
    {
        $expected = PHPUnit_Util_XML::loadFile($expectedFile);
        $actual   = PHPUnit_Util_XML::load($actualXml);

        static::assertEquals($expected, $actual, $message);
    }

    
    public static function assertXmlStringNotEqualsXmlFile($expectedFile, $actualXml, $message = '')
    {
        $expected = PHPUnit_Util_XML::loadFile($expectedFile);
        $actual   = PHPUnit_Util_XML::load($actualXml);

        static::assertNotEquals($expected, $actual, $message);
    }

    
    public static function assertXmlStringEqualsXmlString($expectedXml, $actualXml, $message = '')
    {
        $expected = PHPUnit_Util_XML::load($expectedXml);
        $actual   = PHPUnit_Util_XML::load($actualXml);

        static::assertEquals($expected, $actual, $message);
    }

    
    public static function assertXmlStringNotEqualsXmlString($expectedXml, $actualXml, $message = '')
    {
        $expected = PHPUnit_Util_XML::load($expectedXml);
        $actual   = PHPUnit_Util_XML::load($actualXml);

        static::assertNotEquals($expected, $actual, $message);
    }

    
    public static function assertEqualXMLStructure(DOMElement $expectedElement, DOMElement $actualElement, $checkAttributes = false, $message = '')
    {
        $tmp             = new DOMDocument;
        $expectedElement = $tmp->importNode($expectedElement, true);

        $tmp           = new DOMDocument;
        $actualElement = $tmp->importNode($actualElement, true);

        unset($tmp);

        static::assertEquals(
            $expectedElement->tagName,
            $actualElement->tagName,
            $message
        );

        if ($checkAttributes) {
            static::assertEquals(
                $expectedElement->attributes->length,
                $actualElement->attributes->length,
                sprintf(
                    '%s%sNumber of attributes on node "%s" does not match',
                    $message,
                    !empty($message) ? "\n" : '',
                    $expectedElement->tagName
                )
            );

            for ($i = 0; $i < $expectedElement->attributes->length; $i++) {
                $expectedAttribute = $expectedElement->attributes->item($i);
                $actualAttribute   = $actualElement->attributes->getNamedItem(
                    $expectedAttribute->name
                );

                if (!$actualAttribute) {
                    static::fail(
                        sprintf(
                            '%s%sCould not find attribute "%s" on node "%s"',
                            $message,
                            !empty($message) ? "\n" : '',
                            $expectedAttribute->name,
                            $expectedElement->tagName
                        )
                    );
                }
            }
        }

        PHPUnit_Util_XML::removeCharacterDataNodes($expectedElement);
        PHPUnit_Util_XML::removeCharacterDataNodes($actualElement);

        static::assertEquals(
            $expectedElement->childNodes->length,
            $actualElement->childNodes->length,
            sprintf(
                '%s%sNumber of child nodes of "%s" differs',
                $message,
                !empty($message) ? "\n" : '',
                $expectedElement->tagName
            )
        );

        for ($i = 0; $i < $expectedElement->childNodes->length; $i++) {
            static::assertEqualXMLStructure(
                $expectedElement->childNodes->item($i),
                $actualElement->childNodes->item($i),
                $checkAttributes,
                $message
            );
        }
    }

    
    public static function assertThat($value, PHPUnit_Framework_Constraint $constraint, $message = '')
    {
        self::$count += count($constraint);

        $constraint->evaluate($value, $message);
    }

    
    public static function assertJson($actualJson, $message = '')
    {
        if (!is_string($actualJson)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        static::assertThat($actualJson, static::isJson(), $message);
    }

    
    public static function assertJsonStringEqualsJsonString($expectedJson, $actualJson, $message = '')
    {
        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);

        $expected = json_decode($expectedJson);
        $actual   = json_decode($actualJson);

        static::assertEquals($expected, $actual, $message);
    }

    
    public static function assertJsonStringNotEqualsJsonString($expectedJson, $actualJson, $message = '')
    {
        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);

        $expected = json_decode($expectedJson);
        $actual   = json_decode($actualJson);

        static::assertNotEquals($expected, $actual, $message);
    }

    
    public static function assertJsonStringEqualsJsonFile($expectedFile, $actualJson, $message = '')
    {
        static::assertFileExists($expectedFile, $message);
        $expectedJson = file_get_contents($expectedFile);

        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);

        // call constraint
        $constraint = new PHPUnit_Framework_Constraint_JsonMatches(
            $expectedJson
        );

        static::assertThat($actualJson, $constraint, $message);
    }

    
    public static function assertJsonStringNotEqualsJsonFile($expectedFile, $actualJson, $message = '')
    {
        static::assertFileExists($expectedFile, $message);
        $expectedJson = file_get_contents($expectedFile);

        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);

        // call constraint
        $constraint = new PHPUnit_Framework_Constraint_JsonMatches(
            $expectedJson
        );

        static::assertThat($actualJson, new PHPUnit_Framework_Constraint_Not($constraint), $message);
    }

    
    public static function assertJsonFileEqualsJsonFile($expectedFile, $actualFile, $message = '')
    {
        static::assertFileExists($expectedFile, $message);
        static::assertFileExists($actualFile, $message);

        $actualJson   = file_get_contents($actualFile);
        $expectedJson = file_get_contents($expectedFile);

        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);

        // call constraint
        $constraintExpected = new PHPUnit_Framework_Constraint_JsonMatches(
            $expectedJson
        );

        $constraintActual = new PHPUnit_Framework_Constraint_JsonMatches($actualJson);

        static::assertThat($expectedJson, $constraintActual, $message);
        static::assertThat($actualJson, $constraintExpected, $message);
    }

    
    public static function assertJsonFileNotEqualsJsonFile($expectedFile, $actualFile, $message = '')
    {
        static::assertFileExists($expectedFile, $message);
        static::assertFileExists($actualFile, $message);

        $actualJson   = file_get_contents($actualFile);
        $expectedJson = file_get_contents($expectedFile);

        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);

        // call constraint
        $constraintExpected = new PHPUnit_Framework_Constraint_JsonMatches(
            $expectedJson
        );

        $constraintActual = new PHPUnit_Framework_Constraint_JsonMatches($actualJson);

        static::assertThat($expectedJson, new PHPUnit_Framework_Constraint_Not($constraintActual), $message);
        static::assertThat($actualJson, new PHPUnit_Framework_Constraint_Not($constraintExpected), $message);
    }

    
    public static function logicalAnd()
    {
        $constraints = func_get_args();

        $constraint = new PHPUnit_Framework_Constraint_And;
        $constraint->setConstraints($constraints);

        return $constraint;
    }

    
    public static function logicalOr()
    {
        $constraints = func_get_args();

        $constraint = new PHPUnit_Framework_Constraint_Or;
        $constraint->setConstraints($constraints);

        return $constraint;
    }

    
    public static function logicalNot(PHPUnit_Framework_Constraint $constraint)
    {
        return new PHPUnit_Framework_Constraint_Not($constraint);
    }

    
    public static function logicalXor()
    {
        $constraints = func_get_args();

        $constraint = new PHPUnit_Framework_Constraint_Xor;
        $constraint->setConstraints($constraints);

        return $constraint;
    }

    
    public static function anything()
    {
        return new PHPUnit_Framework_Constraint_IsAnything;
    }

    
    public static function isTrue()
    {
        return new PHPUnit_Framework_Constraint_IsTrue;
    }

    
    public static function callback($callback)
    {
        return new PHPUnit_Framework_Constraint_Callback($callback);
    }

    
    public static function isFalse()
    {
        return new PHPUnit_Framework_Constraint_IsFalse;
    }

    
    public static function isJson()
    {
        return new PHPUnit_Framework_Constraint_IsJson;
    }

    
    public static function isNull()
    {
        return new PHPUnit_Framework_Constraint_IsNull;
    }

    
    public static function isFinite()
    {
        return new PHPUnit_Framework_Constraint_IsFinite;
    }

    
    public static function isInfinite()
    {
        return new PHPUnit_Framework_Constraint_IsInfinite;
    }

    
    public static function isNan()
    {
        return new PHPUnit_Framework_Constraint_IsNan;
    }

    
    public static function attribute(PHPUnit_Framework_Constraint $constraint, $attributeName)
    {
        return new PHPUnit_Framework_Constraint_Attribute(
            $constraint,
            $attributeName
        );
    }

    
    public static function contains($value, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
    {
        return new PHPUnit_Framework_Constraint_TraversableContains($value, $checkForObjectIdentity, $checkForNonObjectIdentity);
    }

    
    public static function containsOnly($type)
    {
        return new PHPUnit_Framework_Constraint_TraversableContainsOnly($type);
    }

    
    public static function containsOnlyInstancesOf($classname)
    {
        return new PHPUnit_Framework_Constraint_TraversableContainsOnly($classname, false);
    }

    
    public static function arrayHasKey($key)
    {
        return new PHPUnit_Framework_Constraint_ArrayHasKey($key);
    }

    
    public static function equalTo($value, $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        return new PHPUnit_Framework_Constraint_IsEqual(
            $value,
            $delta,
            $maxDepth,
            $canonicalize,
            $ignoreCase
        );
    }

    
    public static function attributeEqualTo($attributeName, $value, $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
    {
        return static::attribute(
            static::equalTo(
                $value,
                $delta,
                $maxDepth,
                $canonicalize,
                $ignoreCase
            ),
            $attributeName
        );
    }

    
    public static function isEmpty()
    {
        return new PHPUnit_Framework_Constraint_IsEmpty;
    }

    
    public static function isWritable()
    {
        return new PHPUnit_Framework_Constraint_IsWritable;
    }

    
    public static function isReadable()
    {
        return new PHPUnit_Framework_Constraint_IsReadable;
    }

    
    public static function directoryExists()
    {
        return new PHPUnit_Framework_Constraint_DirectoryExists;
    }

    
    public static function fileExists()
    {
        return new PHPUnit_Framework_Constraint_FileExists;
    }

    
    public static function greaterThan($value)
    {
        return new PHPUnit_Framework_Constraint_GreaterThan($value);
    }

    
    public static function greaterThanOrEqual($value)
    {
        return static::logicalOr(
            new PHPUnit_Framework_Constraint_IsEqual($value),
            new PHPUnit_Framework_Constraint_GreaterThan($value)
        );
    }

    
    public static function classHasAttribute($attributeName)
    {
        return new PHPUnit_Framework_Constraint_ClassHasAttribute(
            $attributeName
        );
    }

    
    public static function classHasStaticAttribute($attributeName)
    {
        return new PHPUnit_Framework_Constraint_ClassHasStaticAttribute(
            $attributeName
        );
    }

    
    public static function objectHasAttribute($attributeName)
    {
        return new PHPUnit_Framework_Constraint_ObjectHasAttribute(
            $attributeName
        );
    }

    
    public static function identicalTo($value)
    {
        return new PHPUnit_Framework_Constraint_IsIdentical($value);
    }

    
    public static function isInstanceOf($className)
    {
        return new PHPUnit_Framework_Constraint_IsInstanceOf($className);
    }

    
    public static function isType($type)
    {
        return new PHPUnit_Framework_Constraint_IsType($type);
    }

    
    public static function lessThan($value)
    {
        return new PHPUnit_Framework_Constraint_LessThan($value);
    }

    
    public static function lessThanOrEqual($value)
    {
        return static::logicalOr(
            new PHPUnit_Framework_Constraint_IsEqual($value),
            new PHPUnit_Framework_Constraint_LessThan($value)
        );
    }

    
    public static function matchesRegularExpression($pattern)
    {
        return new PHPUnit_Framework_Constraint_PCREMatch($pattern);
    }

    
    public static function matches($string)
    {
        return new PHPUnit_Framework_Constraint_StringMatches($string);
    }

    
    public static function stringStartsWith($prefix)
    {
        return new PHPUnit_Framework_Constraint_StringStartsWith($prefix);
    }

    
    public static function stringContains($string, $case = true)
    {
        return new PHPUnit_Framework_Constraint_StringContains($string, $case);
    }

    
    public static function stringEndsWith($suffix)
    {
        return new PHPUnit_Framework_Constraint_StringEndsWith($suffix);
    }

    
    public static function countOf($count)
    {
        return new PHPUnit_Framework_Constraint_Count($count);
    }
    
    public static function fail($message = '')
    {
        throw new PHPUnit_Framework_AssertionFailedError($message);
    }

    
    public static function readAttribute($classOrObject, $attributeName)
    {
        if (!is_string($attributeName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'string');
        }

        if (!preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'valid attribute name');
        }

        if (is_string($classOrObject)) {
            if (!class_exists($classOrObject)) {
                throw PHPUnit_Util_InvalidArgumentHelper::factory(
                    1,
                    'class name'
                );
            }

            return static::getStaticAttribute(
                $classOrObject,
                $attributeName
            );
        } elseif (is_object($classOrObject)) {
            return static::getObjectAttribute(
                $classOrObject,
                $attributeName
            );
        } else {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(
                1,
                'class name or object'
            );
        }
    }

    
    public static function getStaticAttribute($className, $attributeName)
    {
        if (!is_string($className)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
        }

        if (!class_exists($className)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'class name');
        }

        if (!is_string($attributeName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'string');
        }

        if (!preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'valid attribute name');
        }

        $class = new ReflectionClass($className);

        while ($class) {
            $attributes = $class->getStaticProperties();

            if (array_key_exists($attributeName, $attributes)) {
                return $attributes[$attributeName];
            }

            $class = $class->getParentClass();
        }

        throw new PHPUnit_Framework_Exception(
            sprintf(
                'Attribute "%s" not found in class.',
                $attributeName
            )
        );
    }

    
    public static function getObjectAttribute($object, $attributeName)
    {
        if (!is_object($object)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'object');
        }

        if (!is_string($attributeName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'string');
        }

        if (!preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName)) {
            throw PHPUnit_Util_InvalidArgumentHelper::factory(2, 'valid attribute name');
        }

        try {
            $attribute = new ReflectionProperty($object, $attributeName);
        } catch (ReflectionException $e) {
            $reflector = new ReflectionObject($object);

            while ($reflector = $reflector->getParentClass()) {
                try {
                    $attribute = $reflector->getProperty($attributeName);
                    break;
                } catch (ReflectionException $e) {
                }
            }
        }

        if (isset($attribute)) {
            if (!$attribute || $attribute->isPublic()) {
                return $object->$attributeName;
            }

            $attribute->setAccessible(true);
            $value = $attribute->getValue($object);
            $attribute->setAccessible(false);

            return $value;
        }

        throw new PHPUnit_Framework_Exception(
            sprintf(
                'Attribute "%s" not found in object.',
                $attributeName
            )
        );
    }

    
    public static function markTestIncomplete($message = '')
    {
        throw new PHPUnit_Framework_IncompleteTestError($message);
    }

    
    public static function markTestSkipped($message = '')
    {
        throw new PHPUnit_Framework_SkippedTestError($message);
    }

    
    public static function getCount()
    {
        return self::$count;
    }

    
    public static function resetCount()
    {
        self::$count = 0;
    }
}
