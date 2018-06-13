<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use Webmozart\Assert\Assert;


final class Source extends BaseTag implements Factory\StaticMethod
{
    
    protected $name = 'source';

    
    private $startingLine = 1;

    
    private $lineCount = null;

    public function __construct($startingLine, $lineCount = null, Description $description = null)
    {
        Assert::integerish($startingLine);
        Assert::nullOrIntegerish($lineCount);

        $this->startingLine = (int)$startingLine;
        $this->lineCount    = $lineCount !== null ? (int)$lineCount : null;
        $this->description  = $description;
    }

    
    public static function create($body, DescriptionFactory $descriptionFactory = null, TypeContext $context = null)
    {
        Assert::stringNotEmpty($body);
        Assert::notNull($descriptionFactory);

        $startingLine = 1;
        $lineCount    = null;
        $description  = null;

        // Starting line / Number of lines / Description
        if (preg_match('/^([1-9]\d*)\s*(?:((?1))\s+)?(.*)$/sux', $body, $matches)) {
            $startingLine = (int)$matches[1];
            if (isset($matches[2]) && $matches[2] !== '') {
                $lineCount = (int)$matches[2];
            }
            $description = $matches[3];
        }

        return new static($startingLine, $lineCount, $descriptionFactory->create($description, $context));
    }

    
    public function getStartingLine()
    {
        return $this->startingLine;
    }

    
    public function getLineCount()
    {
        return $this->lineCount;
    }

    public function __toString()
    {
        return $this->startingLine
        . ($this->lineCount !== null ? ' ' . $this->lineCount : '')
        . ($this->description ? ' ' . $this->description->render() : '');
    }
}
