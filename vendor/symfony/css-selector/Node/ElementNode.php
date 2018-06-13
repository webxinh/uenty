<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\CssSelector\Node;


class ElementNode extends AbstractNode
{
    
    private $namespace;

    
    private $element;

    
    public function __construct($namespace = null, $element = null)
    {
        $this->namespace = $namespace;
        $this->element = $element;
    }

    
    public function getNamespace()
    {
        return $this->namespace;
    }

    
    public function getElement()
    {
        return $this->element;
    }

    
    public function getSpecificity()
    {
        return new Specificity(0, 0, $this->element ? 1 : 0);
    }

    
    public function __toString()
    {
        $element = $this->element ?: '*';

        return sprintf('%s[%s]', $this->getNodeName(), $this->namespace ? $this->namespace.'|'.$element : $element);
    }
}
