<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DomCrawler;

use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Field\FormField;


class Form extends Link implements \ArrayAccess
{
    
    private $button;

    
    private $fields;

    
    private $baseHref;

    
    public function __construct(\DOMElement $node, $currentUri, $method = null, $baseHref = null)
    {
        parent::__construct($node, $currentUri, $method);
        $this->baseHref = $baseHref;

        $this->initialize();
    }

    
    public function getFormNode()
    {
        return $this->node;
    }

    
    public function setValues(array $values)
    {
        foreach ($values as $name => $value) {
            $this->fields->set($name, $value);
        }

        return $this;
    }

    
    public function getValues()
    {
        $values = array();
        foreach ($this->fields->all() as $name => $field) {
            if ($field->isDisabled()) {
                continue;
            }

            if (!$field instanceof Field\FileFormField && $field->hasValue()) {
                $values[$name] = $field->getValue();
            }
        }

        return $values;
    }

    
    public function getFiles()
    {
        if (!in_array($this->getMethod(), array('POST', 'PUT', 'DELETE', 'PATCH'))) {
            return array();
        }

        $files = array();

        foreach ($this->fields->all() as $name => $field) {
            if ($field->isDisabled()) {
                continue;
            }

            if ($field instanceof Field\FileFormField) {
                $files[$name] = $field->getValue();
            }
        }

        return $files;
    }

    
    public function getPhpValues()
    {
        $values = array();
        foreach ($this->getValues() as $name => $value) {
            $qs = http_build_query(array($name => $value), '', '&');
            if (!empty($qs)) {
                parse_str($qs, $expandedValue);
                $varName = substr($name, 0, strlen(key($expandedValue)));
                $values = array_replace_recursive($values, array($varName => current($expandedValue)));
            }
        }

        return $values;
    }

    
    public function getPhpFiles()
    {
        $values = array();
        foreach ($this->getFiles() as $name => $value) {
            $qs = http_build_query(array($name => $value), '', '&');
            if (!empty($qs)) {
                parse_str($qs, $expandedValue);
                $varName = substr($name, 0, strlen(key($expandedValue)));
                $values = array_replace_recursive($values, array($varName => current($expandedValue)));
            }
        }

        return $values;
    }

    
    public function getUri()
    {
        $uri = parent::getUri();

        if (!in_array($this->getMethod(), array('POST', 'PUT', 'DELETE', 'PATCH'))) {
            $query = parse_url($uri, PHP_URL_QUERY);
            $currentParameters = array();
            if ($query) {
                parse_str($query, $currentParameters);
            }

            $queryString = http_build_query(array_merge($currentParameters, $this->getValues()), null, '&');

            $pos = strpos($uri, '?');
            $base = false === $pos ? $uri : substr($uri, 0, $pos);
            $uri = rtrim($base.'?'.$queryString, '?');
        }

        return $uri;
    }

    protected function getRawUri()
    {
        return $this->node->getAttribute('action');
    }

    
    public function getMethod()
    {
        if (null !== $this->method) {
            return $this->method;
        }

        return $this->node->getAttribute('method') ? strtoupper($this->node->getAttribute('method')) : 'GET';
    }

    
    public function has($name)
    {
        return $this->fields->has($name);
    }

    
    public function remove($name)
    {
        $this->fields->remove($name);
    }

    
    public function get($name)
    {
        return $this->fields->get($name);
    }

    
    public function set(FormField $field)
    {
        $this->fields->add($field);
    }

    
    public function all()
    {
        return $this->fields->all();
    }

    
    public function offsetExists($name)
    {
        return $this->has($name);
    }

    
    public function offsetGet($name)
    {
        return $this->fields->get($name);
    }

    
    public function offsetSet($name, $value)
    {
        $this->fields->set($name, $value);
    }

    
    public function offsetUnset($name)
    {
        $this->fields->remove($name);
    }

    
    public function disableValidation()
    {
        foreach ($this->fields->all() as $field) {
            if ($field instanceof Field\ChoiceFormField) {
                $field->disableValidation();
            }
        }

        return $this;
    }

    
    protected function setNode(\DOMElement $node)
    {
        $this->button = $node;
        if ('button' === $node->nodeName || ('input' === $node->nodeName && in_array(strtolower($node->getAttribute('type')), array('submit', 'button', 'image')))) {
            if ($node->hasAttribute('form')) {
                // if the node has the HTML5-compliant 'form' attribute, use it
                $formId = $node->getAttribute('form');
                $form = $node->ownerDocument->getElementById($formId);
                if (null === $form) {
                    throw new \LogicException(sprintf('The selected node has an invalid form attribute (%s).', $formId));
                }
                $this->node = $form;

                return;
            }
            // we loop until we find a form ancestor
            do {
                if (null === $node = $node->parentNode) {
                    throw new \LogicException('The selected node does not have a form ancestor.');
                }
            } while ('form' !== $node->nodeName);
        } elseif ('form' !== $node->nodeName) {
            throw new \LogicException(sprintf('Unable to submit on a "%s" tag.', $node->nodeName));
        }

        $this->node = $node;
    }

    
    private function initialize()
    {
        $this->fields = new FormFieldRegistry();

        $xpath = new \DOMXPath($this->node->ownerDocument);

        // add submitted button if it has a valid name
        if ('form' !== $this->button->nodeName && $this->button->hasAttribute('name') && $this->button->getAttribute('name')) {
            if ('input' == $this->button->nodeName && 'image' == strtolower($this->button->getAttribute('type'))) {
                $name = $this->button->getAttribute('name');
                $this->button->setAttribute('value', '0');

                // temporarily change the name of the input node for the x coordinate
                $this->button->setAttribute('name', $name.'.x');
                $this->set(new Field\InputFormField($this->button));

                // temporarily change the name of the input node for the y coordinate
                $this->button->setAttribute('name', $name.'.y');
                $this->set(new Field\InputFormField($this->button));

                // restore the original name of the input node
                $this->button->setAttribute('name', $name);
            } else {
                $this->set(new Field\InputFormField($this->button));
            }
        }

        // find form elements corresponding to the current form
        if ($this->node->hasAttribute('id')) {
            // corresponding elements are either descendants or have a matching HTML5 form attribute
            $formId = Crawler::xpathLiteral($this->node->getAttribute('id'));

            $fieldNodes = $xpath->query(sprintf('descendant::input[@form=%s] | descendant::button[@form=%s] | descendant::textarea[@form=%s] | descendant::select[@form=%s] | //form[@id=%s]//input[not(@form)] | //form[@id=%s]//button[not(@form)] | //form[@id=%s]//textarea[not(@form)] | //form[@id=%s]//select[not(@form)]', $formId, $formId, $formId, $formId, $formId, $formId, $formId, $formId));
            foreach ($fieldNodes as $node) {
                $this->addField($node);
            }
        } else {
            // do the xpath query with $this->node as the context node, to only find descendant elements
            // however, descendant elements with form attribute are not part of this form
            $fieldNodes = $xpath->query('descendant::input[not(@form)] | descendant::button[not(@form)] | descendant::textarea[not(@form)] | descendant::select[not(@form)]', $this->node);
            foreach ($fieldNodes as $node) {
                $this->addField($node);
            }
        }

        if ($this->baseHref && '' !== $this->node->getAttribute('action')) {
            $this->currentUri = $this->baseHref;
        }
    }

    private function addField(\DOMElement $node)
    {
        if (!$node->hasAttribute('name') || !$node->getAttribute('name')) {
            return;
        }

        $nodeName = $node->nodeName;
        if ('select' == $nodeName || 'input' == $nodeName && 'checkbox' == strtolower($node->getAttribute('type'))) {
            $this->set(new Field\ChoiceFormField($node));
        } elseif ('input' == $nodeName && 'radio' == strtolower($node->getAttribute('type'))) {
            // there may be other fields with the same name that are no choice
            // fields already registered (see https://github.com/symfony/symfony/issues/11689)
            if ($this->has($node->getAttribute('name')) && $this->get($node->getAttribute('name')) instanceof ChoiceFormField) {
                $this->get($node->getAttribute('name'))->addChoice($node);
            } else {
                $this->set(new Field\ChoiceFormField($node));
            }
        } elseif ('input' == $nodeName && 'file' == strtolower($node->getAttribute('type'))) {
            $this->set(new Field\FileFormField($node));
        } elseif ('input' == $nodeName && !in_array(strtolower($node->getAttribute('type')), array('submit', 'button', 'image'))) {
            $this->set(new Field\InputFormField($node));
        } elseif ('textarea' == $nodeName) {
            $this->set(new Field\TextareaFormField($node));
        }
    }
}
