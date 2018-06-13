<?php
/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class PHPUnit_Framework_MockObject_Matcher_AnyParameters extends PHPUnit_Framework_MockObject_Matcher_StatelessInvocation
{
    
    public function toString()
    {
        return 'with any parameters';
    }

    
    public function matches(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        return true;
    }
}
