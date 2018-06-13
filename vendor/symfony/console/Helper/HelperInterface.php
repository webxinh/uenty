<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Helper;


interface HelperInterface
{
    
    public function setHelperSet(HelperSet $helperSet = null);

    
    public function getHelperSet();

    
    public function getName();
}
