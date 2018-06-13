<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\CssSelector\Parser\Shortcut;

use Symfony\Component\CssSelector\Node\ElementNode;
use Symfony\Component\CssSelector\Node\SelectorNode;
use Symfony\Component\CssSelector\Parser\ParserInterface;


class EmptyStringParser implements ParserInterface
{
    
    public function parse($source)
    {
        // Matches an empty string
        if ($source == '') {
            return array(new SelectorNode(new ElementNode(null, '*')));
        }

        return array();
    }
}
