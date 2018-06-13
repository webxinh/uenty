<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use phpDocumentor\Reflection\FqsenResolver;
use Webmozart\Assert\Assert;


final class Covers extends BaseTag implements Factory\StaticMethod
{
    protected $name = 'covers';

    
    private $refers = null;

    
    public function __construct(Fqsen $refers, Description $description = null)
    {
        $this->refers = $refers;
        $this->description = $description;
    }

    
    public static function create(
        $body,
        DescriptionFactory $descriptionFactory = null,
        FqsenResolver $resolver = null,
        TypeContext $context = null
    )
    {
        Assert::string($body);
        Assert::notEmpty($body);

        $parts = preg_split('/\s+/Su', $body, 2);

        return new static(
            $resolver->resolve($parts[0], $context),
            $descriptionFactory->create(isset($parts[1]) ? $parts[1] : '', $context)
        );
    }

    
    public function getReference()
    {
        return $this->refers;
    }

    
    public function __toString()
    {
        return $this->refers . ($this->description ? ' ' . $this->description->render() : '');
    }
}
