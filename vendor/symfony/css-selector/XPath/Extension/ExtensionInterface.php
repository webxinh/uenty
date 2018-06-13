<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\CssSelector\XPath\Extension;


interface ExtensionInterface
{
    
    public function getNodeTranslators();

    
    public function getCombinationTranslators();

    
    public function getFunctionTranslators();

    
    public function getPseudoClassTranslators();

    
    public function getAttributeMatchingTranslators();

    
    public function getName();
}
