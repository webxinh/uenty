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


class HHVM extends Xdebug
{
    
    public function start($determineUnusedAndDead = true)
    {
        xdebug_start_code_coverage();
    }
}
