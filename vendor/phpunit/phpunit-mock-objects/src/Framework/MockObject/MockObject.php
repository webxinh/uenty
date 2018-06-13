<?php
/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface PHPUnit_Framework_MockObject_MockObject /*extends PHPUnit_Framework_MockObject_Verifiable*/
{
    
    public function expects(PHPUnit_Framework_MockObject_Matcher_Invocation $matcher);

    
    public function __phpunit_setOriginalObject($originalObject);

    
    public function __phpunit_getInvocationMocker();

    
    public function __phpunit_verify();

    
    public function __phpunit_hasMatchers();
}
