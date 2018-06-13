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


class InputFormField extends FormField
{
    
    protected function initialize()
    {
        if ('input' !== $this->node->nodeName && 'button' !== $this->node->nodeName) {
            throw new \LogicException(sprintf('An InputFormField can only be created from an input or button tag (%s given).', $this->node->nodeName));
        }

        if ('checkbox' === strtolower($this->node->getAttribute('type'))) {
            throw new \LogicException('Checkboxes should be instances of ChoiceFormField.');
        }

        if ('file' === strtolower($this->node->getAttribute('type'))) {
            throw new \LogicException('File inputs should be instances of FileFormField.');
        }

        $this->value = $this->node->getAttribute('value');
    }
}
