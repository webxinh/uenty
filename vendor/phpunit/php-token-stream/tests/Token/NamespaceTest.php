<?php
/*
 * This file is part of the PHP_TokenStream package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHP_Token_NamespaceTest extends PHPUnit_Framework_TestCase
{
    
    public function testGetName()
    {
        $tokenStream = new PHP_Token_Stream(
          TEST_FILES_PATH . 'classInNamespace.php'
        );

        foreach ($tokenStream as $token) {
            if ($token instanceof PHP_Token_NAMESPACE) {
                $this->assertSame('Foo\\Bar', $token->getName());
            }
        }
    }

    public function testGetStartLineWithUnscopedNamespace()
    {
        $tokenStream = new PHP_Token_Stream(TEST_FILES_PATH . 'classInNamespace.php');
        foreach($tokenStream as $token) {
            if($token instanceOf PHP_Token_NAMESPACE) {
                $this->assertSame(2, $token->getLine());
            }
        }
    }

    public function testGetEndLineWithUnscopedNamespace()
    {
        $tokenStream = new PHP_Token_Stream(TEST_FILES_PATH . 'classInNamespace.php');
        foreach($tokenStream as $token) {
            if($token instanceOf PHP_Token_NAMESPACE) {
                $this->assertSame(2, $token->getEndLine());
            }
        }
    }
    public function testGetStartLineWithScopedNamespace()
    {
        $tokenStream = new PHP_Token_Stream(TEST_FILES_PATH . 'classInScopedNamespace.php');
        foreach($tokenStream as $token) {
            if($token instanceOf PHP_Token_NAMESPACE) {
                $this->assertSame(2, $token->getLine());
            }
        }
    }

    public function testGetEndLineWithScopedNamespace()
    {
        $tokenStream = new PHP_Token_Stream(TEST_FILES_PATH . 'classInScopedNamespace.php');
        foreach($tokenStream as $token) {
            if($token instanceOf PHP_Token_NAMESPACE) {
                $this->assertSame(8, $token->getEndLine());
            }
        }
    }

}
