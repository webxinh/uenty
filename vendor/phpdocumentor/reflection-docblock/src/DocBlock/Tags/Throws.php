<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;


final class Throws extends BaseTag implements Factory\StaticMethod
{
    protected $name = 'throws';

    
    private $type;

    public function __construct(Type $type, Description $description = null)
    {
        $this->type        = $type;
        $this->description = $description;
    }

    
    public static function create(
        $body,
        TypeResolver $typeResolver = null,
        DescriptionFactory $descriptionFactory = null,
        TypeContext $context = null
    ) {
        Assert::string($body);
        Assert::allNotNull([$typeResolver, $descriptionFactory]);

        $parts = preg_split('/\s+/Su', $body, 2);

        $type        = $typeResolver->resolve(isset($parts[0]) ? $parts[0] : '', $context);
        $description = $descriptionFactory->create(isset($parts[1]) ? $parts[1] : '', $context);

        return new static($type, $description);
    }

    
    public function getType()
    {
        return $this->type;
    }

    public function __toString()
    {
        return $this->type . ' ' . $this->description;
    }
}
