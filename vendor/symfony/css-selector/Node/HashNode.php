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


class HashNode extends AbstractNode
{
    
    private $selector;

    
    private $id;

    
    public function __construct(NodeInterface $selector, $id)
    {
        $this->selector = $selector;
        $this->id = $id;
    }

    
    public function getSelector()
    {
        return $this->selector;
    }

    
    public function getId()
    {
        return $this->id;
    }

    
    public function getSpecificity()
    {
        return $this->selector->getSpecificity()->plus(new Specificity(1, 0, 0));
    }

    
    public function __toString()
    {
        return sprintf('%s[%s#%s]', $this->getNodeName(), $this->selector, $this->id);
    }
}
