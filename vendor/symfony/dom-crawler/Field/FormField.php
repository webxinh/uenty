<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DomCrawler\Field;


abstract class FormField
{
    
    protected $node;
    
    protected $name;
    
    protected $value;
    
    protected $document;
    
    protected $xpath;
    
    protected $disabled;

    
    public function __construct(\DOMElement $node)
    {
        $this->node = $node;
        $this->name = $node->getAttribute('name');
        $this->xpath = new \DOMXPath($node->ownerDocument);

        $this->initialize();
    }

    
    public function getLabel()
    {
        $xpath = new \DOMXPath($this->node->ownerDocument);

        if ($this->node->hasAttribute('id')) {
            $labels = $xpath->query(sprintf('descendant::label[@for="%s"]', $this->node->getAttribute('id')));
            if ($labels->length > 0) {
                return $labels->item(0);
            }
        }

        $labels = $xpath->query('ancestor::label[1]', $this->node);
        if ($labels->length > 0) {
            return $labels->item(0);
        }

        return;
    }

    
    public function getName()
    {
        return $this->name;
    }

    
    public function getValue()
    {
        return $this->value;
    }

    
    public function setValue($value)
    {
        $this->value = (string) $value;
    }

    
    public function hasValue()
    {
        return true;
    }

    
    public function isDisabled()
    {
        return $this->node->hasAttribute('disabled');
    }

    
    abstract protected function initialize();
}
