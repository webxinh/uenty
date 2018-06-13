<?php
namespace Codeception\Util;


class XmlBuilder
{
    
    protected $__dom__;

    
    protected $__currentNode__;


    public function __construct()
    {
        $this->__dom__ = new \DOMDocument();
        $this->__currentNode__ = $this->__dom__;
    }

    
    public function __get($tag)
    {
        $node = $this->__dom__->createElement($tag);
        $this->__currentNode__->appendChild($node);
        $this->__currentNode__ = $node;
        return $this;
    }

    
    public function val($val)
    {
        $this->__currentNode__->nodeValue = $val;
        return $this;
    }

    
    public function attr($attr, $val)
    {
        $this->__currentNode__->setAttribute($attr, $val);
        return $this;
    }

    
    public function parent()
    {
        $this->__currentNode__ = $this->__currentNode__->parentNode;
        return $this;
    }

    
    public function parents($tag)
    {
        $traverseNode = $this->__currentNode__;
        $elFound = false;
        while ($traverseNode->parentNode) {
            $traverseNode = $traverseNode->parentNode;
            if ($traverseNode->tagName == $tag) {
                $this->__currentNode__ = $traverseNode;
                $elFound = true;
                break;
            }
        }

        if (!$elFound) {
            throw new \Exception("Parent $tag not found in XML");
        }

        return $this;
    }

    public function __toString()
    {
        return $this->__dom__->saveXML();
    }

    
    public function getDom()
    {
        return $this->__dom__;
    }
}
