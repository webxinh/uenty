<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;


final class Link extends BaseTag implements Factory\StaticMethod
{
    protected $name = 'link';

    
    private $link = '';

    
    public function __construct($link, Description $description = null)
    {
        Assert::string($link);

        $this->link = $link;
        $this->description = $description;
    }

    
    public static function create($body, DescriptionFactory $descriptionFactory = null, TypeContext $context = null)
    {
        Assert::string($body);
        Assert::notNull($descriptionFactory);

        $parts = preg_split('/\s+/Su', $body, 2);
        $description = isset($parts[1]) ? $descriptionFactory->create($parts[1], $context) : null;

        return new static($parts[0], $description);
    }

    
    public function getLink()
    {
        return $this->link;
    }

    
    public function __toString()
    {
        return $this->link . ($this->description ? ' ' . $this->description->render() : '');
    }
}
