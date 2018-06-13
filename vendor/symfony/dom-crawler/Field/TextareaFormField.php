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


class TextareaFormField extends FormField
{
    
    protected function initialize()
    {
        if ('textarea' !== $this->node->nodeName) {
            throw new \LogicException(sprintf('A TextareaFormField can only be created from a textarea tag (%s given).', $this->node->nodeName));
        }

        $this->value = '';
        foreach ($this->node->childNodes as $node) {
            $this->value .= $node->wholeText;
        }
    }
}
