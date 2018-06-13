<?php
namespace Codeception\Util\Shared;

trait Asserts
{
    protected function assert($arguments, $not = false)
    {
        $not = $not ? 'Not' : '';
        $method = ucfirst(array_shift($arguments));
        if (($method === 'True') && $not) {
            $method = 'False';
            $not = '';
        }
        if (($method === 'False') && $not) {
            $method = 'True';
            $not = '';
        }

        call_user_func_array(['\PHPUnit_Framework_Assert', 'assert' . $not . $method], $arguments);
    }

    protected function assertNot($arguments)
    {
        $this->assert($arguments, true);
    }

    
    protected function assertEquals($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertEquals($expected, $actual, $message);
    }

    
    protected function assertNotEquals($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotEquals($expected, $actual, $message);
    }

    
    protected function assertSame($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertSame($expected, $actual, $message);
    }

    
    protected function assertNotSame($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotSame($expected, $actual, $message);
    }

    
    protected function assertGreaterThan($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertGreaterThan($expected, $actual, $message);
    }

    
    protected function assertGreaterThen($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertGreaterThan($expected, $actual, $message);
    }

    
    protected function assertGreaterThanOrEqual($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertGreaterThanOrEqual($expected, $actual, $message);
    }

    
    protected function assertGreaterThenOrEqual($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertGreaterThanOrEqual($expected, $actual, $message);
    }

    
    protected function assertLessThan($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertLessThan($expected, $actual, $message);
    }

    
    protected function assertLessThanOrEqual($expected, $actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertLessThanOrEqual($expected, $actual, $message);
    }


    
    protected function assertContains($needle, $haystack, $message = '')
    {
        \PHPUnit_Framework_Assert::assertContains($needle, $haystack, $message);
    }

    
    protected function assertNotContains($needle, $haystack, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotContains($needle, $haystack, $message);
    }

    
    protected function assertRegExp($pattern, $string, $message = '')
    {
        \PHPUnit_Framework_Assert::assertRegExp($pattern, $string, $message);
    }
    
    
    protected function assertNotRegExp($pattern, $string, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotRegExp($pattern, $string, $message);
    }
        
    
    
    protected function assertEmpty($actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertEmpty($actual, $message);
    }

    
    protected function assertNotEmpty($actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotEmpty($actual, $message);
    }

    
    protected function assertNull($actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNull($actual, $message);
    }

    
    protected function assertNotNull($actual, $message = '')
    {
        \PHPUnit_Framework_Assert::assertNotNull($actual, $message);
    }

    
    protected function assertTrue($condition, $message = '')
    {
        \PHPUnit_Framework_Assert::assertTrue($condition, $message);
    }

    
    protected function assertFalse($condition, $message = '')
    {
        \PHPUnit_Framework_Assert::assertFalse($condition, $message);
    }

    
    protected function assertThat($haystack, $constraint, $message = '')
    {
        \PHPUnit_Framework_Assert::assertThat($haystack, $constraint, $message);
    }

    
    protected function assertThatItsNot($haystack, $constraint, $message = '')
    {
        $constraint = new \PHPUnit_Framework_Constraint_Not($constraint);
        \PHPUnit_Framework_Assert::assertThat($haystack, $constraint, $message);
    }

    
    
    protected function assertFileExists($filename, $message = '')
    {
        \PHPUnit_Framework_Assert::assertFileExists($filename, $message);
    }
    
        
    
    protected function assertFileNotExists($filename, $message = '')
    {
        \PHPUnit_Framework_Assert::assertFileNotExists($filename, $message);
    }

    
    protected function assertGreaterOrEquals($expected, $actual, $description = '')
    {
        \PHPUnit_Framework_Assert::assertGreaterThanOrEqual($expected, $actual, $description);
    }

    
    protected function assertLessOrEquals($expected, $actual, $description = '')
    {
        \PHPUnit_Framework_Assert::assertLessThanOrEqual($expected, $actual, $description);
    }

    
    protected function assertIsEmpty($actual, $description = '')
    {
        \PHPUnit_Framework_Assert::assertEmpty($actual, $description);
    }

    
    protected function assertArrayHasKey($key, $actual, $description = '')
    {
        \PHPUnit_Framework_Assert::assertArrayHasKey($key, $actual, $description);
    }

    
    protected function assertArrayNotHasKey($key, $actual, $description = '')
    {
        \PHPUnit_Framework_Assert::assertArrayNotHasKey($key, $actual, $description);
    }

    
    protected function assertCount($expectedCount, $actual, $description = '')
    {
        \PHPUnit_Framework_Assert::assertCount($expectedCount, $actual, $description);
    }

    
    protected function assertInstanceOf($class, $actual, $description = '')
    {
        \PHPUnit_Framework_Assert::assertInstanceOf($class, $actual, $description);
    }

    
    protected function assertNotInstanceOf($class, $actual, $description = '')
    {
        \PHPUnit_Framework_Assert::assertNotInstanceOf($class, $actual, $description);
    }

    
    protected function assertInternalType($type, $actual, $description = '')
    {
        \PHPUnit_Framework_Assert::assertInternalType($type, $actual, $description);
    }
    
    
    protected function fail($message)
    {
        \PHPUnit_Framework_Assert::fail($message);
    }
}
