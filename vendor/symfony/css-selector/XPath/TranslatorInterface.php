<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\CssSelector\XPath;

use Symfony\Component\CssSelector\Node\SelectorNode;


interface TranslatorInterface
{
    
    public function cssToXPath($cssExpr, $prefix = 'descendant-or-self::');

    
    public function selectorToXPath(SelectorNode $selector, $prefix = 'descendant-or-self::');
}
