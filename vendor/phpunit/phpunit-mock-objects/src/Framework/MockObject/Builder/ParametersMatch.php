<?php
/*
 * This file is part of the PHPUnit_MockObject package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


interface PHPUnit_Framework_MockObject_Builder_ParametersMatch extends PHPUnit_Framework_MockObject_Builder_Match
{
    
    public function with(...$arguments);

    
    public function withAnyParameters();
}
