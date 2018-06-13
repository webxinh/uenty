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


abstract class AbstractExtension implements ExtensionInterface
{
    
    public function getNodeTranslators()
    {
        return array();
    }

    
    public function getCombinationTranslators()
    {
        return array();
    }

    
    public function getFunctionTranslators()
    {
        return array();
    }

    
    public function getPseudoClassTranslators()
    {
        return array();
    }

    
    public function getAttributeMatchingTranslators()
    {
        return array();
    }
}
