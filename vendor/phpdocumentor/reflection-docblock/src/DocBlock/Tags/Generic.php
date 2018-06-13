<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;


class Generic extends BaseTag implements Factory\StaticMethod
{
    
    public function __construct($name, Description $description = null)
    {
        $this->validateTagName($name);

        $this->name = $name;
        $this->description = $description;
    }

    
    public static function create(
        $body,
        $name = '',
        DescriptionFactory $descriptionFactory = null,
        TypeContext $context = null
    ) {
        Assert::string($body);
        Assert::stringNotEmpty($name);
        Assert::notNull($descriptionFactory);

        $description = $descriptionFactory && $body ? $descriptionFactory->create($body, $context) : null;

        return new static($name, $description);
    }

    
    public function __toString()
    {
        return ($this->description ? $this->description->render() : '');
    }

    
    private function validateTagName($name)
    {
        if (! preg_match('/^' . StandardTagFactory::REGEX_TAGNAME . '$/u', $name)) {
            throw new \InvalidArgumentException(
                'The tag name "' . $name . '" is not wellformed. Tags may only consist of letters, underscores, '
                . 'hyphens and backslashes.'
            );
        }
    }
}
