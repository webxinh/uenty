<?php


require_once(__DIR__ . '/../vendor/autoload.php');

use phpDocumentor\Reflection\DocBlock\Serializer;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\StaticMethod;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tags\BaseTag;
use phpDocumentor\Reflection\Types\Context;
use Webmozart\Assert\Assert;


final class MyTag extends BaseTag implements StaticMethod
{
    
    protected $name = 'my-tag';

    
    public function __construct(Description $description = null)
    {
        $this->description = $description;
    }

    
    public static function create($body, DescriptionFactory $descriptionFactory = null, Context $context = null)
    {
        Assert::string($body);
        Assert::notNull($descriptionFactory);

        return new static($descriptionFactory->create($body, $context));
    }

    
    public function __toString()
    {
        return (string)$this->description;
    }
}

$docComment = <<<DOCCOMMENT

DOCCOMMENT;

// Make a mapping between the tag name `my-tag` and the Tag class containing the Factory Method `create`.
$customTags = ['my-tag' => MyTag::class];

// Do pass the list of custom tags to the Factory for the DocBlockFactory.
$factory = DocBlockFactory::createInstance($customTags);
// You can also add Tags later using `$factory->registerTagHandler()` with a tag name and Tag class name.

// Create the DocBlock
$docblock = $factory->create($docComment);

// Take a look: the $customTagObjects now contain an array with your newly added tag
$customTagObjects = $docblock->getTagsByName('my-tag');

// As an experiment: let's reconstitute the DocBlock and observe that because we added a __toString() method
// to the tag class that we can now also see it.
$serializer              = new Serializer();
$reconstitutedDocComment = $serializer->getDocComment($docblock);
