<?php
namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;


class Asserts extends CodeceptionModule
{

    
    public function assertEquals($expected, $actual, $message = '')
    {
        parent::assertEquals($expected, $actual, $message);
    }

    
    public function assertNotEquals($expected, $actual, $message = '')
    {
        parent::assertNotEquals($expected, $actual, $message);
    }

    
    public function assertSame($expected, $actual, $message = '')
    {
        parent::assertSame($expected, $actual, $message);
    }

    
    public function assertNotSame($expected, $actual, $message = '')
    {
        parent::assertNotSame($expected, $actual, $message);
    }

    
    public function assertGreaterThan($expected, $actual, $message = '')
    {
        parent::assertGreaterThan($expected, $actual, $message);
    }

    
    public function assertGreaterThanOrEqual($expected, $actual, $message = '')
    {
        parent::assertGreaterThanOrEqual($expected, $actual, $message);
    }

    
    public function assertLessThan($expected, $actual, $message = '')
    {
        parent::assertLessThan($expected, $actual, $message);
    }

    
    public function assertLessThanOrEqual($expected, $actual, $message = '')
    {
        parent::assertLessThanOrEqual($expected, $actual, $message);
    }

    
    public function assertContains($needle, $haystack, $message = '')
    {
        parent::assertContains($needle, $haystack, $message);
    }

    
    public function assertNotContains($needle, $haystack, $message = '')
    {
        parent::assertNotContains($needle, $haystack, $message);
    }

    
    public function assertRegExp($pattern, $string, $message = '')
    {
        parent::assertRegExp($pattern, $string, $message);
    }

    
    public function assertNotRegExp($pattern, $string, $message = '')
    {
        parent::assertNotRegExp($pattern, $string, $message);
    }


    
    public function assertEmpty($actual, $message = '')
    {
        parent::assertEmpty($actual, $message);
    }

    
    public function assertNotEmpty($actual, $message = '')
    {
        parent::assertNotEmpty($actual, $message);
    }

    
    public function assertNull($actual, $message = '')
    {
        parent::assertNull($actual, $message);
    }

    
    public function assertNotNull($actual, $message = '')
    {
        parent::assertNotNull($actual, $message);
    }

    
    public function assertTrue($condition, $message = '')
    {
        parent::assertTrue($condition, $message);
    }

    
    public function assertFalse($condition, $message = '')
    {
        parent::assertFalse($condition, $message);
    }

    
    public function assertFileExists($filename, $message = '')
    {
        parent::assertFileExists($filename, $message);
    }
    
    
    public function assertFileNotExists($filename, $message = '')
    {
        parent::assertFileNotExists($filename, $message);
    }

    
    public function assertGreaterOrEquals($expected, $actual, $description = '')
    {
        $this->assertGreaterThanOrEqual($expected, $actual, $description);
    }

    
    public function assertLessOrEquals($expected, $actual, $description = '')
    {
        $this->assertLessThanOrEqual($expected, $actual, $description);
    }

    
    public function assertIsEmpty($actual, $description = '')
    {
        $this->assertEmpty($actual, $description);
    }

    
    public function assertArrayHasKey($key, $actual, $description = '')
    {
        parent::assertArrayHasKey($key, $actual, $description);
    }

    
    public function assertArrayNotHasKey($key, $actual, $description = '')
    {
        parent::assertArrayNotHasKey($key, $actual, $description);
    }

    
    public function assertCount($expectedCount, $actual, $description = '')
    {
        parent::assertCount($expectedCount, $actual, $description);
    }

    
    public function assertInstanceOf($class, $actual, $description = '')
    {
        parent::assertInstanceOf($class, $actual, $description);
    }

    
    public function assertNotInstanceOf($class, $actual, $description = '')
    {
        parent::assertNotInstanceOf($class, $actual, $description);
    }

    
    public function assertInternalType($type, $actual, $description = '')
    {
        parent::assertInternalType($type, $actual, $description);
    }

    
    public function fail($message)
    {
        parent::fail($message);
    }

    
    public function expectException($exception, $callback)
    {
        $code = null;
        $msg = null;
        if (is_object($exception)) {
            
             $class = get_class($exception);
            $msg = $exception->getMessage();
            $code = $exception->getCode();
        } else {
            $class = $exception;
        }
        try {
            $callback();
        } catch (\Exception $e) {
            if (!$e instanceof $class) {
                $this->fail(sprintf("Exception of class $class expected to be thrown, but %s caught", get_class($e)));
            }
            if (null !== $msg and $e->getMessage() !== $msg) {
                $this->fail(sprintf(
                    "Exception of $class expected to be '$msg', but actual message was '%s'",
                    $e->getMessage()
                ));
            }
            if (null !== $code and $e->getCode() !== $code) {
                $this->fail(sprintf(
                    "Exception of $class expected to have code $code, but actual code was %s",
                    $e->getCode()
                ));
            }
            $this->assertTrue(true); // increment assertion counter
             return;
        }
        $this->fail("Expected exception to be thrown, but nothing was caught");
    }
}
