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

use Symfony\Component\CssSelector\CssSelectorConverter;


class Crawler implements \Countable, \IteratorAggregate
{
    
    protected $uri;

    
    private $defaultNamespacePrefix = 'default';

    
    private $namespaces = array();

    
    private $baseHref;

    
    private $document;

    
    private $nodes = array();

    
    private $isHtml = true;

    
    public function __construct($node = null, $currentUri = null, $baseHref = null)
    {
        $this->uri = $currentUri;
        $this->baseHref = $baseHref ?: $currentUri;

        $this->add($node);
    }

    
    public function getUri()
    {
        return $this->uri;
    }

    
    public function getBaseHref()
    {
        return $this->baseHref;
    }

    
    public function clear()
    {
        $this->nodes = array();
        $this->document = null;
    }

    
    public function add($node)
    {
        if ($node instanceof \DOMNodeList) {
            $this->addNodeList($node);
        } elseif ($node instanceof \DOMNode) {
            $this->addNode($node);
        } elseif (is_array($node)) {
            $this->addNodes($node);
        } elseif (is_string($node)) {
            $this->addContent($node);
        } elseif (null !== $node) {
            throw new \InvalidArgumentException(sprintf('Expecting a DOMNodeList or DOMNode instance, an array, a string, or null, but got "%s".', is_object($node) ? get_class($node) : gettype($node)));
        }
    }

    
    public function addContent($content, $type = null)
    {
        if (empty($type)) {
            $type = 0 === strpos($content, '<?xml') ? 'application/xml' : 'text/html';
        }

        // DOM only for HTML/XML content
        if (!preg_match('/(x|ht)ml/i', $type, $xmlMatches)) {
            return;
        }

        $charset = null;
        if (false !== $pos = stripos($type, 'charset=')) {
            $charset = substr($type, $pos + 8);
            if (false !== $pos = strpos($charset, ';')) {
                $charset = substr($charset, 0, $pos);
            }
        }

        // http://www.w3.org/TR/encoding/#encodings
        // http://www.w3.org/TR/REC-xml/#NT-EncName
        if (null === $charset &&
            preg_match('/\<meta[^\>]+charset *= *["\']?([a-zA-Z\-0-9_:.]+)/i', $content, $matches)) {
            $charset = $matches[1];
        }

        if (null === $charset) {
            $charset = 'ISO-8859-1';
        }

        if ('x' === $xmlMatches[1]) {
            $this->addXmlContent($content, $charset);
        } else {
            $this->addHtmlContent($content, $charset);
        }
    }

    
    public function addHtmlContent($content, $charset = 'UTF-8')
    {
        $internalErrors = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);

        $dom = new \DOMDocument('1.0', $charset);
        $dom->validateOnParse = true;

        set_error_handler(function () {throw new \Exception();});

        try {
            // Convert charset to HTML-entities to work around bugs in DOMDocument::loadHTML()
            $content = mb_convert_encoding($content, 'HTML-ENTITIES', $charset);
        } catch (\Exception $e) {
        }

        restore_error_handler();

        if ('' !== trim($content)) {
            @$dom->loadHTML($content);
        }

        libxml_use_internal_errors($internalErrors);
        libxml_disable_entity_loader($disableEntities);

        $this->addDocument($dom);

        $base = $this->filterRelativeXPath('descendant-or-self::base')->extract(array('href'));

        $baseHref = current($base);
        if (count($base) && !empty($baseHref)) {
            if ($this->baseHref) {
                $linkNode = $dom->createElement('a');
                $linkNode->setAttribute('href', $baseHref);
                $link = new Link($linkNode, $this->baseHref);
                $this->baseHref = $link->getUri();
            } else {
                $this->baseHref = $baseHref;
            }
        }
    }

    
    public function addXmlContent($content, $charset = 'UTF-8', $options = LIBXML_NONET)
    {
        // remove the default namespace if it's the only namespace to make XPath expressions simpler
        if (!preg_match('/xmlns:/', $content)) {
            $content = str_replace('xmlns', 'ns', $content);
        }

        $internalErrors = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);

        $dom = new \DOMDocument('1.0', $charset);
        $dom->validateOnParse = true;

        if ('' !== trim($content)) {
            @$dom->loadXML($content, $options);
        }

        libxml_use_internal_errors($internalErrors);
        libxml_disable_entity_loader($disableEntities);

        $this->addDocument($dom);

        $this->isHtml = false;
    }

    
    public function addDocument(\DOMDocument $dom)
    {
        if ($dom->documentElement) {
            $this->addNode($dom->documentElement);
        }
    }

    
    public function addNodeList(\DOMNodeList $nodes)
    {
        foreach ($nodes as $node) {
            if ($node instanceof \DOMNode) {
                $this->addNode($node);
            }
        }
    }

    
    public function addNodes(array $nodes)
    {
        foreach ($nodes as $node) {
            $this->add($node);
        }
    }

    
    public function addNode(\DOMNode $node)
    {
        if ($node instanceof \DOMDocument) {
            $node = $node->documentElement;
        }

        if (null !== $this->document && $this->document !== $node->ownerDocument) {
            throw new \InvalidArgumentException('Attaching DOM nodes from multiple documents in the same crawler is forbidden.');
        }

        if (null === $this->document) {
            $this->document = $node->ownerDocument;
        }

        // Don't add duplicate nodes in the Crawler
        if (in_array($node, $this->nodes, true)) {
            return;
        }

        $this->nodes[] = $node;
    }

    
    public function eq($position)
    {
        if (isset($this->nodes[$position])) {
            return $this->createSubCrawler($this->nodes[$position]);
        }

        return $this->createSubCrawler(null);
    }

    
    public function each(\Closure $closure)
    {
        $data = array();
        foreach ($this->nodes as $i => $node) {
            $data[] = $closure($this->createSubCrawler($node), $i);
        }

        return $data;
    }

    
    public function slice($offset = 0, $length = null)
    {
        return $this->createSubCrawler(array_slice($this->nodes, $offset, $length));
    }

    
    public function reduce(\Closure $closure)
    {
        $nodes = array();
        foreach ($this->nodes as $i => $node) {
            if (false !== $closure($this->createSubCrawler($node), $i)) {
                $nodes[] = $node;
            }
        }

        return $this->createSubCrawler($nodes);
    }

    
    public function first()
    {
        return $this->eq(0);
    }

    
    public function last()
    {
        return $this->eq(count($this->nodes) - 1);
    }

    
    public function siblings()
    {
        if (!$this->nodes) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        return $this->createSubCrawler($this->sibling($this->getNode(0)->parentNode->firstChild));
    }

    
    public function nextAll()
    {
        if (!$this->nodes) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        return $this->createSubCrawler($this->sibling($this->getNode(0)));
    }

    
    public function previousAll()
    {
        if (!$this->nodes) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        return $this->createSubCrawler($this->sibling($this->getNode(0), 'previousSibling'));
    }

    
    public function parents()
    {
        if (!$this->nodes) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        $node = $this->getNode(0);
        $nodes = array();

        while ($node = $node->parentNode) {
            if (XML_ELEMENT_NODE === $node->nodeType) {
                $nodes[] = $node;
            }
        }

        return $this->createSubCrawler($nodes);
    }

    
    public function children()
    {
        if (!$this->nodes) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        $node = $this->getNode(0)->firstChild;

        return $this->createSubCrawler($node ? $this->sibling($node) : array());
    }

    
    public function attr($attribute)
    {
        if (!$this->nodes) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        $node = $this->getNode(0);

        return $node->hasAttribute($attribute) ? $node->getAttribute($attribute) : null;
    }

    
    public function nodeName()
    {
        if (!$this->nodes) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        return $this->getNode(0)->nodeName;
    }

    
    public function text()
    {
        if (!$this->nodes) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        return $this->getNode(0)->nodeValue;
    }

    
    public function html()
    {
        if (!$this->nodes) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        $html = '';
        foreach ($this->getNode(0)->childNodes as $child) {
            $html .= $child->ownerDocument->saveHTML($child);
        }

        return $html;
    }

    
    public function evaluate($xpath)
    {
        if (null === $this->document) {
            throw new \LogicException('Cannot evaluate the expression on an uninitialized crawler.');
        }

        $data = array();
        $domxpath = $this->createDOMXPath($this->document, $this->findNamespacePrefixes($xpath));

        foreach ($this->nodes as $node) {
            $data[] = $domxpath->evaluate($xpath, $node);
        }

        if (isset($data[0]) && $data[0] instanceof \DOMNodeList) {
            return $this->createSubCrawler($data);
        }

        return $data;
    }

    
    public function extract($attributes)
    {
        $attributes = (array) $attributes;
        $count = count($attributes);

        $data = array();
        foreach ($this->nodes as $node) {
            $elements = array();
            foreach ($attributes as $attribute) {
                if ('_text' === $attribute) {
                    $elements[] = $node->nodeValue;
                } else {
                    $elements[] = $node->getAttribute($attribute);
                }
            }

            $data[] = $count > 1 ? $elements : $elements[0];
        }

        return $data;
    }

    
    public function filterXPath($xpath)
    {
        $xpath = $this->relativize($xpath);

        // If we dropped all expressions in the XPath while preparing it, there would be no match
        if ('' === $xpath) {
            return $this->createSubCrawler(null);
        }

        return $this->filterRelativeXPath($xpath);
    }

    
    public function filter($selector)
    {
        if (!class_exists('Symfony\\Component\\CssSelector\\CssSelectorConverter')) {
            throw new \RuntimeException('Unable to filter with a CSS selector as the Symfony CssSelector 2.8+ is not installed (you can use filterXPath instead).');
        }

        $converter = new CssSelectorConverter($this->isHtml);

        // The CssSelector already prefixes the selector with descendant-or-self::
        return $this->filterRelativeXPath($converter->toXPath($selector));
    }

    
    public function selectLink($value)
    {
        $xpath = sprintf('descendant-or-self::a[contains(concat(\' \', normalize-space(string(.)), \' \'), %s) ', static::xpathLiteral(' '.$value.' ')).
                            sprintf('or ./img[contains(concat(\' \', normalize-space(string(@alt)), \' \'), %s)]]', static::xpathLiteral(' '.$value.' '));

        return $this->filterRelativeXPath($xpath);
    }

    
    public function selectImage($value)
    {
        $xpath = sprintf('descendant-or-self::img[contains(normalize-space(string(@alt)), %s)]', static::xpathLiteral($value));

        return $this->filterRelativeXPath($xpath);
    }

    
    public function selectButton($value)
    {
        $translate = 'translate(@type, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")';
        $xpath = sprintf('descendant-or-self::input[((contains(%s, "submit") or contains(%s, "button")) and contains(concat(\' \', normalize-space(string(@value)), \' \'), %s)) ', $translate, $translate, static::xpathLiteral(' '.$value.' ')).
                         sprintf('or (contains(%s, "image") and contains(concat(\' \', normalize-space(string(@alt)), \' \'), %s)) or @id=%s or @name=%s] ', $translate, static::xpathLiteral(' '.$value.' '), static::xpathLiteral($value), static::xpathLiteral($value)).
                         sprintf('| descendant-or-self::button[contains(concat(\' \', normalize-space(string(.)), \' \'), %s) or @id=%s or @name=%s]', static::xpathLiteral(' '.$value.' '), static::xpathLiteral($value), static::xpathLiteral($value));

        return $this->filterRelativeXPath($xpath);
    }

    
    public function link($method = 'get')
    {
        if (!$this->nodes) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        $node = $this->getNode(0);

        if (!$node instanceof \DOMElement) {
            throw new \InvalidArgumentException(sprintf('The selected node should be instance of DOMElement, got "%s".', get_class($node)));
        }

        return new Link($node, $this->baseHref, $method);
    }

    
    public function links()
    {
        $links = array();
        foreach ($this->nodes as $node) {
            if (!$node instanceof \DOMElement) {
                throw new \InvalidArgumentException(sprintf('The current node list should contain only DOMElement instances, "%s" found.', get_class($node)));
            }

            $links[] = new Link($node, $this->baseHref, 'get');
        }

        return $links;
    }

    
    public function image()
    {
        if (!count($this)) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        $node = $this->getNode(0);

        if (!$node instanceof \DOMElement) {
            throw new \InvalidArgumentException(sprintf('The selected node should be instance of DOMElement, got "%s".', get_class($node)));
        }

        return new Image($node, $this->baseHref);
    }

    
    public function images()
    {
        $images = array();
        foreach ($this as $node) {
            if (!$node instanceof \DOMElement) {
                throw new \InvalidArgumentException(sprintf('The current node list should contain only DOMElement instances, "%s" found.', get_class($node)));
            }

            $images[] = new Image($node, $this->baseHref);
        }

        return $images;
    }

    
    public function form(array $values = null, $method = null)
    {
        if (!$this->nodes) {
            throw new \InvalidArgumentException('The current node list is empty.');
        }

        $node = $this->getNode(0);

        if (!$node instanceof \DOMElement) {
            throw new \InvalidArgumentException(sprintf('The selected node should be instance of DOMElement, got "%s".', get_class($node)));
        }

        $form = new Form($node, $this->uri, $method, $this->baseHref);

        if (null !== $values) {
            $form->setValues($values);
        }

        return $form;
    }

    
    public function setDefaultNamespacePrefix($prefix)
    {
        $this->defaultNamespacePrefix = $prefix;
    }

    
    public function registerNamespace($prefix, $namespace)
    {
        $this->namespaces[$prefix] = $namespace;
    }

    
    public static function xpathLiteral($s)
    {
        if (false === strpos($s, "'")) {
            return sprintf("'%s'", $s);
        }

        if (false === strpos($s, '"')) {
            return sprintf('"%s"', $s);
        }

        $string = $s;
        $parts = array();
        while (true) {
            if (false !== $pos = strpos($string, "'")) {
                $parts[] = sprintf("'%s'", substr($string, 0, $pos));
                $parts[] = "\"'\"";
                $string = substr($string, $pos + 1);
            } else {
                $parts[] = "'$string'";
                break;
            }
        }

        return sprintf('concat(%s)', implode(', ', $parts));
    }

    
    private function filterRelativeXPath($xpath)
    {
        $prefixes = $this->findNamespacePrefixes($xpath);

        $crawler = $this->createSubCrawler(null);

        foreach ($this->nodes as $node) {
            $domxpath = $this->createDOMXPath($node->ownerDocument, $prefixes);
            $crawler->add($domxpath->query($xpath, $node));
        }

        return $crawler;
    }

    
    private function relativize($xpath)
    {
        $expressions = array();

        // An expression which will never match to replace expressions which cannot match in the crawler
        // We cannot simply drop
        $nonMatchingExpression = 'a[name() = "b"]';

        $xpathLen = strlen($xpath);
        $openedBrackets = 0;
        $startPosition = strspn($xpath, " \t\n\r\0\x0B");

        for ($i = $startPosition; $i <= $xpathLen; ++$i) {
            $i += strcspn($xpath, '"\'[]|', $i);

            if ($i < $xpathLen) {
                switch ($xpath[$i]) {
                    case '"':
                    case "'":
                        if (false === $i = strpos($xpath, $xpath[$i], $i + 1)) {
                            return $xpath; // The XPath expression is invalid
                        }
                        continue 2;
                    case '[':
                        ++$openedBrackets;
                        continue 2;
                    case ']':
                        --$openedBrackets;
                        continue 2;
                }
            }
            if ($openedBrackets) {
                continue;
            }

            if ($startPosition < $xpathLen && '(' === $xpath[$startPosition]) {
                // If the union is inside some braces, we need to preserve the opening braces and apply
                // the change only inside it.
                $j = 1 + strspn($xpath, "( \t\n\r\0\x0B", $startPosition + 1);
                $parenthesis = substr($xpath, $startPosition, $j);
                $startPosition += $j;
            } else {
                $parenthesis = '';
            }
            $expression = rtrim(substr($xpath, $startPosition, $i - $startPosition));

            if (0 === strpos($expression, 'self::*/')) {
                $expression = './'.substr($expression, 8);
            }

            // add prefix before absolute element selector
            if ('' === $expression) {
                $expression = $nonMatchingExpression;
            } elseif (0 === strpos($expression, '//')) {
                $expression = 'descendant-or-self::'.substr($expression, 2);
            } elseif (0 === strpos($expression, './/')) {
                $expression = 'descendant-or-self::'.substr($expression, 3);
            } elseif (0 === strpos($expression, './')) {
                $expression = 'self::'.substr($expression, 2);
            } elseif (0 === strpos($expression, 'child::')) {
                $expression = 'self::'.substr($expression, 7);
            } elseif ('/' === $expression[0] || '.' === $expression[0] || 0 === strpos($expression, 'self::')) {
                $expression = $nonMatchingExpression;
            } elseif (0 === strpos($expression, 'descendant::')) {
                $expression = 'descendant-or-self::'.substr($expression, 12);
            } elseif (preg_match('/^(ancestor|ancestor-or-self|attribute|following|following-sibling|namespace|parent|preceding|preceding-sibling)::/', $expression)) {
                // the fake root has no parent, preceding or following nodes and also no attributes (even no namespace attributes)
                $expression = $nonMatchingExpression;
            } elseif (0 !== strpos($expression, 'descendant-or-self::')) {
                $expression = 'self::'.$expression;
            }
            $expressions[] = $parenthesis.$expression;

            if ($i === $xpathLen) {
                return implode(' | ', $expressions);
            }

            $i += strspn($xpath, " \t\n\r\0\x0B", $i + 1);
            $startPosition = $i + 1;
        }

        return $xpath; // The XPath expression is invalid
    }

    
    public function getNode($position)
    {
        if (isset($this->nodes[$position])) {
            return $this->nodes[$position];
        }
    }

    
    public function count()
    {
        return count($this->nodes);
    }

    
    public function getIterator()
    {
        return new \ArrayIterator($this->nodes);
    }

    
    protected function sibling($node, $siblingDir = 'nextSibling')
    {
        $nodes = array();

        do {
            if ($node !== $this->getNode(0) && $node->nodeType === 1) {
                $nodes[] = $node;
            }
        } while ($node = $node->$siblingDir);

        return $nodes;
    }

    
    private function createDOMXPath(\DOMDocument $document, array $prefixes = array())
    {
        $domxpath = new \DOMXPath($document);

        foreach ($prefixes as $prefix) {
            $namespace = $this->discoverNamespace($domxpath, $prefix);
            if (null !== $namespace) {
                $domxpath->registerNamespace($prefix, $namespace);
            }
        }

        return $domxpath;
    }

    
    private function discoverNamespace(\DOMXPath $domxpath, $prefix)
    {
        if (isset($this->namespaces[$prefix])) {
            return $this->namespaces[$prefix];
        }

        // ask for one namespace, otherwise we'd get a collection with an item for each node
        $namespaces = $domxpath->query(sprintf('(//namespace::*[name()="%s"])[last()]', $this->defaultNamespacePrefix === $prefix ? '' : $prefix));

        if ($node = $namespaces->item(0)) {
            return $node->nodeValue;
        }
    }

    
    private function findNamespacePrefixes($xpath)
    {
        if (preg_match_all('/(?P<prefix>[a-z_][a-z_0-9\-\.]*+):[^"\/:]/i', $xpath, $matches)) {
            return array_unique($matches['prefix']);
        }

        return array();
    }

    
    private function createSubCrawler($nodes)
    {
        $crawler = new static($nodes, $this->uri, $this->baseHref);
        $crawler->isHtml = $this->isHtml;
        $crawler->document = $this->document;
        $crawler->namespaces = $this->namespaces;

        return $crawler;
    }
}
