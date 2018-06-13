<?php
/*
 * This file is part of the PHP_TokenStream package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHP_Token_ClassTest extends PHPUnit_Framework_TestCase
{
    protected $class;
    protected $function;

    protected function setUp()
    {
        $ts = new PHP_Token_Stream(TEST_FILES_PATH . 'source2.php');

        foreach ($ts as $token) {
            if ($token instanceof PHP_Token_CLASS) {
                $this->class = $token;
            }

            if ($token instanceof PHP_Token_FUNCTION) {
                $this->function = $token;
                break;
            }
        }
    }

    
    public function testGetClassKeywords()
    {
        $this->assertEquals('abstract', $this->class->getKeywords());
    }

    
    public function testGetFunctionKeywords()
    {
        $this->assertEquals('abstract,static', $this->function->getKeywords());
    }

    
    public function testGetFunctionVisibility()
    {
        $this->assertEquals('public', $this->function->getVisibility());
    }

    public function testIssue19()
    {
        $ts = new PHP_Token_Stream(TEST_FILES_PATH . 'issue19.php');

        foreach ($ts as $token) {
            if ($token instanceof PHP_Token_CLASS) {
                $this->assertFalse($token->hasInterfaces());
            }
        }
    }

    public function testIssue30()
    {
        $ts = new PHP_Token_Stream(TEST_FILES_PATH . 'issue30.php');
        $this->assertCount(1, $ts->getClasses());
    }

    
    public function testAnonymousClassesAreHandledCorrectly()
    {
        $ts = new PHP_Token_Stream(TEST_FILES_PATH . 'class_with_method_that_declares_anonymous_class.php');

        $classes = $ts->getClasses();

        $this->assertEquals(array('class_with_method_that_declares_anonymous_class'), array_keys($classes));
    }

    
    public function testAnonymousClassesAreHandledCorrectly2()
    {
        $ts = new PHP_Token_Stream(TEST_FILES_PATH . 'class_with_method_that_declares_anonymous_class2.php');

        $classes = $ts->getClasses();

        $this->assertEquals(array('Test'), array_keys($classes));
        $this->assertEquals(array('methodOne', 'methodTwo'), array_keys($classes['Test']['methods']));

        $this->assertEmpty($ts->getFunctions());
    }

    
    public function testImportedFunctionsAreHandledCorrectly()
    {
        $ts = new PHP_Token_Stream(TEST_FILES_PATH . 'classUsesNamespacedFunction.php');

        $this->assertEmpty($ts->getFunctions());
    }
}
