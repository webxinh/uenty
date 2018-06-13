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


class ClassNode extends AbstractNode
{
    
    private $selector;

    
    private $name;

    
    public function __construct(NodeInterface $selector, $name)
    {
        $this->selector = $selector;
        $this->name = $name;
    }

    
    public function getSelector()
    {
        return $this->selector;
    }

    
    public function getName()
    {
        return $this->name;
    }

    
    public function getSpecificity()
    {
        return $this->selector->getSpecificity()->plus(new Specificity(0, 1, 0));
    }

    
    public function __toString()
    {
        return sprintf('%s[%s.%s]', $this->getNodeName(), $this->selector, $this->name);
    }
}
