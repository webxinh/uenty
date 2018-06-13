<?php


namespace phpDocumentor\Reflection;

use phpDocumentor\Reflection\DocBlock\Tag;
use Webmozart\Assert\Assert;

final class DocBlock
{
    
    private $summary = '';

    
    private $description = null;

    
    private $tags = array();

    
    private $context = null;

    
    private $location = null;

    
    private $isTemplateStart = false;

    
    private $isTemplateEnd = false;

    
    public function __construct(
        $summary = '',
        DocBlock\Description $description = null,
        array $tags = [],
        Types\Context $context = null,
        Location $location = null,
        $isTemplateStart = false,
        $isTemplateEnd = false
    )
    {
        Assert::string($summary);
        Assert::boolean($isTemplateStart);
        Assert::boolean($isTemplateEnd);
        Assert::allIsInstanceOf($tags, Tag::class);

        $this->summary = $summary;
        $this->description = $description ?: new DocBlock\Description('');
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }

        $this->context = $context;
        $this->location = $location;

        $this->isTemplateEnd = $isTemplateEnd;
        $this->isTemplateStart = $isTemplateStart;
    }

    
    public function getSummary()
    {
        return $this->summary;
    }

    
    public function getDescription()
    {
        return $this->description;
    }

    
    public function getContext()
    {
        return $this->context;
    }

    
    public function getLocation()
    {
        return $this->location;
    }

    
    public function isTemplateStart()
    {
        return $this->isTemplateStart;
    }

    
    public function isTemplateEnd()
    {
        return $this->isTemplateEnd;
    }

    
    public function getTags()
    {
        return $this->tags;
    }

    
    public function getTagsByName($name)
    {
        Assert::string($name);

        $result = array();

        
        foreach ($this->getTags() as $tag) {
            if ($tag->getName() != $name) {
                continue;
            }

            $result[] = $tag;
        }

        return $result;
    }

    
    public function hasTag($name)
    {
        Assert::string($name);

        
        foreach ($this->getTags() as $tag) {
            if ($tag->getName() == $name) {
                return true;
            }
        }

        return false;
    }

    
    private function addTag(Tag $tag)
    {
        $this->tags[] = $tag;
    }
}
