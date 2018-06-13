<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class ExceptionMessageTest extends PHPUnit_Framework_TestCase
{
    
    public function testLiteralMessage()
    {
        throw new Exception('A literal exception message');
    }

    
    public function testPatialMessageBegin()
    {
        throw new Exception('A partial exception message');
    }

    
    public function testPatialMessageMiddle()
    {
        throw new Exception('A partial exception message');
    }

    
    public function testPatialMessageEnd()
    {
        throw new Exception('A partial exception message');
    }
}
