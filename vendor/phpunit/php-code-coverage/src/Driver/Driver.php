<?php
/*
 * This file is part of the php-code-coverage package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\CodeCoverage\Driver;


interface Driver
{
    
    const LINE_EXECUTED = 1;

    
    const LINE_NOT_EXECUTED = -1;

    
    const LINE_NOT_EXECUTABLE = -2;

    
    public function start($determineUnusedAndDead = true);

    
    public function stop();
}
