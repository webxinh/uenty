<?php
/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


abstract class PHPUnit_Framework_MockObject_Matcher_StatelessInvocation implements PHPUnit_Framework_MockObject_Matcher_Invocation
{
    
    public function invoked(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
    }

    
    public function verify()
    {
    }
}
