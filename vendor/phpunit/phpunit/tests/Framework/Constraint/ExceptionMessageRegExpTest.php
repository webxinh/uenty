<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class ExceptionMessageRegExpTest extends PHPUnit_Framework_TestCase
{
    
    public function testRegexMessage()
    {
        throw new Exception('A polymorphic exception message');
    }

    
    public function testRegexMessageExtreme()
    {
        throw new Exception('A polymorphic exception message');
    }

    
    public function testMessageXdebugScreamCompatibility()
    {
        ini_set('xdebug.scream', '1');
        throw new Exception('Screaming preg_match');
    }

    
    public function testSimultaneousLiteralAndRegExpExceptionMessage()
    {
        throw new Exception('A variadic exception message');
    }
}
