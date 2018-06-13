<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\FqsenResolver;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;


final class Uses extends BaseTag implements Factory\StaticMethod
{
    protected $name = 'uses';

    
    protected $refers = null;

    
    public function __construct(Fqsen $refers, Description $description = null)
    {
        $this->refers      = $refers;
        $this->description = $description;
    }

    
    public static function create(
        $body,
        FqsenResolver $resolver = null,
        DescriptionFactory $descriptionFactory = null,
        TypeContext $context = null
    ) {
        Assert::string($body);
        Assert::allNotNull([$resolver, $descriptionFactory]);

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
        return $this->refers . ' ' . $this->description->render();
    }
}
