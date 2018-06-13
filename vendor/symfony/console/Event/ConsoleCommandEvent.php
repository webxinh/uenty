<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Event;


class ConsoleCommandEvent extends ConsoleEvent
{
    
    const RETURN_CODE_DISABLED = 113;

    
    private $commandShouldRun = true;

    
    public function disableCommand()
    {
        return $this->commandShouldRun = false;
    }

    
    public function enableCommand()
    {
        return $this->commandShouldRun = true;
    }

    
    public function commandShouldRun()
    {
        return $this->commandShouldRun;
    }
}
