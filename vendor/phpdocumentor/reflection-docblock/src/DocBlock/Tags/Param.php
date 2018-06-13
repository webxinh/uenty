<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;


final class Param extends BaseTag implements Factory\StaticMethod
{
    
    protected $name = 'param';

    
    private $type;

    
    private $variableName = '';

    
    private $isVariadic = false;

    
    public function __construct($variableName, Type $type = null, $isVariadic = false, Description $description = null)
    {
        Assert::string($variableName);
        Assert::boolean($isVariadic);

        $this->variableName = $variableName;
        $this->type = $type;
        $this->isVariadic = $isVariadic;
        $this->description = $description;
    }

    
    public static function create(
        $body,
        TypeResolver $typeResolver = null,
        DescriptionFactory $descriptionFactory = null,
        TypeContext $context = null
    ) {
        Assert::stringNotEmpty($body);
        Assert::allNotNull([$typeResolver, $descriptionFactory]);

        $parts = preg_split('/(\s+)/Su', $body, 3, PREG_SPLIT_DELIM_CAPTURE);
        $type = null;
        $variableName = '';
        $isVariadic = false;

        // if the first item that is encountered is not a variable; it is a type
        if (isset($parts[0]) && (strlen($parts[0]) > 0) && ($parts[0][0] !== '$')) {
            $type = $typeResolver->resolve(array_shift($parts), $context);
            array_shift($parts);
        }

        // if the next item starts with a $ or ...$ it must be the variable name
        if (isset($parts[0]) && (strlen($parts[0]) > 0) && ($parts[0][0] == '$' || substr($parts[0], 0, 4) === '...$')) {
            $variableName = array_shift($parts);
            array_shift($parts);

            if (substr($variableName, 0, 3) === '...') {
                $isVariadic = true;
                $variableName = substr($variableName, 3);
            }

            if (substr($variableName, 0, 1) === '$') {
                $variableName = substr($variableName, 1);
            }
        }

        $description = $descriptionFactory->create(implode('', $parts), $context);

        return new static($variableName, $type, $isVariadic, $description);
    }

    
    public function getVariableName()
    {
        return $this->variableName;
    }

    
    public function getType()
    {
        return $this->type;
    }

    
    public function isVariadic()
    {
        return $this->isVariadic;
    }

    
    public function __toString()
    {
        return ($this->type ? $this->type . ' ' : '')
        . ($this->isVariadic() ? '...' : '')
        . '$' . $this->variableName
        . ($this->description ? ' ' . $this->description : '');
    }
}
