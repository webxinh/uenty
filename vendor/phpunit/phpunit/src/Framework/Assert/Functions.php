<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


function any()
{
    return call_user_func_array(
        'PHPUnit_Framework_TestCase::any',
        func_get_args()
    );
}


function anything()
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::anything',
        func_get_args()
    );
}


function arrayHasKey($key)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::arrayHasKey',
        func_get_args()
    );
}


function assertArrayHasKey($key, $array, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertArrayHasKey',
        func_get_args()
    );
}


function assertArraySubset($subset, $array, $strict = false, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertArraySubset',
        func_get_args()
    );
}


function assertArrayNotHasKey($key, $array, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertArrayNotHasKey',
        func_get_args()
    );
}


function assertAttributeContains($needle, $haystackAttributeName, $haystackClassOrObject, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeContains',
        func_get_args()
    );
}


function assertAttributeContainsOnly($type, $haystackAttributeName, $haystackClassOrObject, $isNativeType = null, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeContainsOnly',
        func_get_args()
    );
}


function assertAttributeCount($expectedCount, $haystackAttributeName, $haystackClassOrObject, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeCount',
        func_get_args()
    );
}


function assertAttributeEmpty($haystackAttributeName, $haystackClassOrObject, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeEmpty',
        func_get_args()
    );
}


function assertAttributeEquals($expected, $actualAttributeName, $actualClassOrObject, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeEquals',
        func_get_args()
    );
}


function assertAttributeGreaterThan($expected, $actualAttributeName, $actualClassOrObject, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeGreaterThan',
        func_get_args()
    );
}


function assertAttributeGreaterThanOrEqual($expected, $actualAttributeName, $actualClassOrObject, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeGreaterThanOrEqual',
        func_get_args()
    );
}


function assertAttributeInstanceOf($expected, $attributeName, $classOrObject, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeInstanceOf',
        func_get_args()
    );
}


function assertAttributeInternalType($expected, $attributeName, $classOrObject, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeInternalType',
        func_get_args()
    );
}


function assertAttributeLessThan($expected, $actualAttributeName, $actualClassOrObject, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeLessThan',
        func_get_args()
    );
}


function assertAttributeLessThanOrEqual($expected, $actualAttributeName, $actualClassOrObject, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeLessThanOrEqual',
        func_get_args()
    );
}


function assertAttributeNotContains($needle, $haystackAttributeName, $haystackClassOrObject, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeNotContains',
        func_get_args()
    );
}


function assertAttributeNotContainsOnly($type, $haystackAttributeName, $haystackClassOrObject, $isNativeType = null, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeNotContainsOnly',
        func_get_args()
    );
}


function assertAttributeNotCount($expectedCount, $haystackAttributeName, $haystackClassOrObject, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeNotCount',
        func_get_args()
    );
}


function assertAttributeNotEmpty($haystackAttributeName, $haystackClassOrObject, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeNotEmpty',
        func_get_args()
    );
}


function assertAttributeNotEquals($expected, $actualAttributeName, $actualClassOrObject, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeNotEquals',
        func_get_args()
    );
}


function assertAttributeNotInstanceOf($expected, $attributeName, $classOrObject, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeNotInstanceOf',
        func_get_args()
    );
}


function assertAttributeNotInternalType($expected, $attributeName, $classOrObject, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeNotInternalType',
        func_get_args()
    );
}


function assertAttributeNotSame($expected, $actualAttributeName, $actualClassOrObject, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeNotSame',
        func_get_args()
    );
}


function assertAttributeSame($expected, $actualAttributeName, $actualClassOrObject, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertAttributeSame',
        func_get_args()
    );
}


function assertClassHasAttribute($attributeName, $className, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertClassHasAttribute',
        func_get_args()
    );
}


function assertClassHasStaticAttribute($attributeName, $className, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertClassHasStaticAttribute',
        func_get_args()
    );
}


function assertClassNotHasAttribute($attributeName, $className, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertClassNotHasAttribute',
        func_get_args()
    );
}


function assertClassNotHasStaticAttribute($attributeName, $className, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertClassNotHasStaticAttribute',
        func_get_args()
    );
}


function assertContains($needle, $haystack, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertContains',
        func_get_args()
    );
}


function assertContainsOnly($type, $haystack, $isNativeType = null, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertContainsOnly',
        func_get_args()
    );
}


function assertContainsOnlyInstancesOf($classname, $haystack, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertContainsOnlyInstancesOf',
        func_get_args()
    );
}


function assertCount($expectedCount, $haystack, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertCount',
        func_get_args()
    );
}


function assertEmpty($actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertEmpty',
        func_get_args()
    );
}


function assertEqualXMLStructure(DOMElement $expectedElement, DOMElement $actualElement, $checkAttributes = false, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertEqualXMLStructure',
        func_get_args()
    );
}


function assertEquals($expected, $actual, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertEquals',
        func_get_args()
    );
}


function assertNotTrue($condition, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertNotTrue',
        func_get_args()
    );
}


function assertFalse($condition, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertFalse',
        func_get_args()
    );
}


function assertFileEquals($expected, $actual, $message = '', $canonicalize = false, $ignoreCase = false)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertFileEquals',
        func_get_args()
    );
}


function assertFileExists($filename, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertFileExists',
        func_get_args()
    );
}


function assertFileNotEquals($expected, $actual, $message = '', $canonicalize = false, $ignoreCase = false)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertFileNotEquals',
        func_get_args()
    );
}


function assertFileNotExists($filename, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertFileNotExists',
        func_get_args()
    );
}


function assertGreaterThan($expected, $actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertGreaterThan',
        func_get_args()
    );
}


function assertGreaterThanOrEqual($expected, $actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertGreaterThanOrEqual',
        func_get_args()
    );
}


function assertInstanceOf($expected, $actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertInstanceOf',
        func_get_args()
    );
}


function assertInternalType($expected, $actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertInternalType',
        func_get_args()
    );
}


function assertJson($actualJson, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertJson',
        func_get_args()
    );
}


function assertJsonFileEqualsJsonFile($expectedFile, $actualFile, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertJsonFileEqualsJsonFile',
        func_get_args()
    );
}


function assertJsonFileNotEqualsJsonFile($expectedFile, $actualFile, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertJsonFileNotEqualsJsonFile',
        func_get_args()
    );
}


function assertJsonStringEqualsJsonFile($expectedFile, $actualJson, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertJsonStringEqualsJsonFile',
        func_get_args()
    );
}


function assertJsonStringEqualsJsonString($expectedJson, $actualJson, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertJsonStringEqualsJsonString',
        func_get_args()
    );
}


function assertJsonStringNotEqualsJsonFile($expectedFile, $actualJson, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertJsonStringNotEqualsJsonFile',
        func_get_args()
    );
}


function assertJsonStringNotEqualsJsonString($expectedJson, $actualJson, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertJsonStringNotEqualsJsonString',
        func_get_args()
    );
}


function assertLessThan($expected, $actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertLessThan',
        func_get_args()
    );
}


function assertLessThanOrEqual($expected, $actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertLessThanOrEqual',
        func_get_args()
    );
}


function assertFinite($actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertFinite',
        func_get_args()
    );
}


function assertInfinite($actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertInfinite',
        func_get_args()
    );
}


function assertNan($actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertNan',
        func_get_args()
    );
}


function assertNotContains($needle, $haystack, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertNotContains',
        func_get_args()
    );
}


function assertNotContainsOnly($type, $haystack, $isNativeType = null, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertNotContainsOnly',
        func_get_args()
    );
}


function assertNotCount($expectedCount, $haystack, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertNotCount',
        func_get_args()
    );
}


function assertNotEmpty($actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertNotEmpty',
        func_get_args()
    );
}


function assertNotEquals($expected, $actual, $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertNotEquals',
        func_get_args()
    );
}


function assertNotInstanceOf($expected, $actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertNotInstanceOf',
        func_get_args()
    );
}


function assertNotInternalType($expected, $actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertNotInternalType',
        func_get_args()
    );
}


function assertNotFalse($condition, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertNotFalse',
        func_get_args()
    );
}


function assertNotNull($actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertNotNull',
        func_get_args()
    );
}


function assertNotRegExp($pattern, $string, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertNotRegExp',
        func_get_args()
    );
}


function assertNotSame($expected, $actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertNotSame',
        func_get_args()
    );
}


function assertNotSameSize($expected, $actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertNotSameSize',
        func_get_args()
    );
}


function assertNull($actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertNull',
        func_get_args()
    );
}


function assertObjectHasAttribute($attributeName, $object, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertObjectHasAttribute',
        func_get_args()
    );
}


function assertObjectNotHasAttribute($attributeName, $object, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertObjectNotHasAttribute',
        func_get_args()
    );
}


function assertRegExp($pattern, $string, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertRegExp',
        func_get_args()
    );
}


function assertSame($expected, $actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertSame',
        func_get_args()
    );
}


function assertSameSize($expected, $actual, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertSameSize',
        func_get_args()
    );
}


function assertStringEndsNotWith($suffix, $string, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertStringEndsNotWith',
        func_get_args()
    );
}


function assertStringEndsWith($suffix, $string, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertStringEndsWith',
        func_get_args()
    );
}


function assertStringEqualsFile($expectedFile, $actualString, $message = '', $canonicalize = false, $ignoreCase = false)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertStringEqualsFile',
        func_get_args()
    );
}


function assertStringMatchesFormat($format, $string, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertStringMatchesFormat',
        func_get_args()
    );
}


function assertStringMatchesFormatFile($formatFile, $string, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertStringMatchesFormatFile',
        func_get_args()
    );
}


function assertStringNotEqualsFile($expectedFile, $actualString, $message = '', $canonicalize = false, $ignoreCase = false)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertStringNotEqualsFile',
        func_get_args()
    );
}


function assertStringNotMatchesFormat($format, $string, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertStringNotMatchesFormat',
        func_get_args()
    );
}


function assertStringNotMatchesFormatFile($formatFile, $string, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertStringNotMatchesFormatFile',
        func_get_args()
    );
}


function assertStringStartsNotWith($prefix, $string, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertStringStartsNotWith',
        func_get_args()
    );
}


function assertStringStartsWith($prefix, $string, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertStringStartsWith',
        func_get_args()
    );
}


function assertThat($value, PHPUnit_Framework_Constraint $constraint, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertThat',
        func_get_args()
    );
}


function assertTrue($condition, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertTrue',
        func_get_args()
    );
}


function assertXmlFileEqualsXmlFile($expectedFile, $actualFile, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertXmlFileEqualsXmlFile',
        func_get_args()
    );
}


function assertXmlFileNotEqualsXmlFile($expectedFile, $actualFile, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertXmlFileNotEqualsXmlFile',
        func_get_args()
    );
}


function assertXmlStringEqualsXmlFile($expectedFile, $actualXml, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertXmlStringEqualsXmlFile',
        func_get_args()
    );
}


function assertXmlStringEqualsXmlString($expectedXml, $actualXml, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertXmlStringEqualsXmlString',
        func_get_args()
    );
}


function assertXmlStringNotEqualsXmlFile($expectedFile, $actualXml, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertXmlStringNotEqualsXmlFile',
        func_get_args()
    );
}


function assertXmlStringNotEqualsXmlString($expectedXml, $actualXml, $message = '')
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::assertXmlStringNotEqualsXmlString',
        func_get_args()
    );
}


function at($index)
{
    return call_user_func_array(
        'PHPUnit_Framework_TestCase::at',
        func_get_args()
    );
}


function atLeastOnce()
{
    return call_user_func_array(
        'PHPUnit_Framework_TestCase::atLeastOnce',
        func_get_args()
    );
}


function attribute(PHPUnit_Framework_Constraint $constraint, $attributeName)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::attribute',
        func_get_args()
    );
}


function attributeEqualTo($attributeName, $value, $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::attributeEqualTo',
        func_get_args()
    );
}


function callback($callback)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::callback',
        func_get_args()
    );
}


function classHasAttribute($attributeName)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::classHasAttribute',
        func_get_args()
    );
}


function classHasStaticAttribute($attributeName)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::classHasStaticAttribute',
        func_get_args()
    );
}


function contains($value, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::contains',
        func_get_args()
    );
}


function containsOnly($type)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::containsOnly',
        func_get_args()
    );
}


function containsOnlyInstancesOf($classname)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::containsOnlyInstancesOf',
        func_get_args()
    );
}


function equalTo($value, $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::equalTo',
        func_get_args()
    );
}


function exactly($count)
{
    return call_user_func_array(
        'PHPUnit_Framework_TestCase::exactly',
        func_get_args()
    );
}


function fileExists()
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::fileExists',
        func_get_args()
    );
}


function greaterThan($value)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::greaterThan',
        func_get_args()
    );
}


function greaterThanOrEqual($value)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::greaterThanOrEqual',
        func_get_args()
    );
}


function identicalTo($value)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::identicalTo',
        func_get_args()
    );
}


function isEmpty()
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::isEmpty',
        func_get_args()
    );
}


function isFalse()
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::isFalse',
        func_get_args()
    );
}


function isInstanceOf($className)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::isInstanceOf',
        func_get_args()
    );
}


function isJson()
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::isJson',
        func_get_args()
    );
}


function isNull()
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::isNull',
        func_get_args()
    );
}


function isTrue()
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::isTrue',
        func_get_args()
    );
}


function isType($type)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::isType',
        func_get_args()
    );
}


function lessThan($value)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::lessThan',
        func_get_args()
    );
}


function lessThanOrEqual($value)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::lessThanOrEqual',
        func_get_args()
    );
}


function logicalAnd()
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::logicalAnd',
        func_get_args()
    );
}


function logicalNot(PHPUnit_Framework_Constraint $constraint)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::logicalNot',
        func_get_args()
    );
}


function logicalOr()
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::logicalOr',
        func_get_args()
    );
}


function logicalXor()
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::logicalXor',
        func_get_args()
    );
}


function matches($string)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::matches',
        func_get_args()
    );
}


function matchesRegularExpression($pattern)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::matchesRegularExpression',
        func_get_args()
    );
}


function never()
{
    return call_user_func_array(
        'PHPUnit_Framework_TestCase::never',
        func_get_args()
    );
}


function objectHasAttribute($attributeName)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::objectHasAttribute',
        func_get_args()
    );
}


function onConsecutiveCalls()
{
    return call_user_func_array(
        'PHPUnit_Framework_TestCase::onConsecutiveCalls',
        func_get_args()
    );
}


function once()
{
    return call_user_func_array(
        'PHPUnit_Framework_TestCase::once',
        func_get_args()
    );
}


function returnArgument($argumentIndex)
{
    return call_user_func_array(
        'PHPUnit_Framework_TestCase::returnArgument',
        func_get_args()
    );
}


function returnCallback($callback)
{
    return call_user_func_array(
        'PHPUnit_Framework_TestCase::returnCallback',
        func_get_args()
    );
}


function returnSelf()
{
    return call_user_func_array(
        'PHPUnit_Framework_TestCase::returnSelf',
        func_get_args()
    );
}


function returnValue($value)
{
    return call_user_func_array(
        'PHPUnit_Framework_TestCase::returnValue',
        func_get_args()
    );
}


function returnValueMap(array $valueMap)
{
    return call_user_func_array(
        'PHPUnit_Framework_TestCase::returnValueMap',
        func_get_args()
    );
}


function stringContains($string, $case = true)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::stringContains',
        func_get_args()
    );
}


function stringEndsWith($suffix)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::stringEndsWith',
        func_get_args()
    );
}


function stringStartsWith($prefix)
{
    return call_user_func_array(
        'PHPUnit_Framework_Assert::stringStartsWith',
        func_get_args()
    );
}


function throwException(Exception $exception)
{
    return call_user_func_array(
        'PHPUnit_Framework_TestCase::throwException',
        func_get_args()
    );
}
