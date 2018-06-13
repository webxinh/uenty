<?php


namespace phpDocumentor\Reflection\DocBlock;

use phpDocumentor\Reflection\DocBlock;
use Webmozart\Assert\Assert;


class Serializer
{
    
    protected $indentString = ' ';

    
    protected $indent = 0;

    
    protected $isFirstLineIndented = true;

    
    protected $lineLength = null;

    
    public function __construct($indent = 0, $indentString = ' ', $indentFirstLine = true, $lineLength = null)
    {
        Assert::integer($indent);
        Assert::string($indentString);
        Assert::boolean($indentFirstLine);
        Assert::nullOrInteger($lineLength);

        $this->indent = $indent;
        $this->indentString = $indentString;
        $this->isFirstLineIndented = $indentFirstLine;
        $this->lineLength = $lineLength;
    }

    
    public function getDocComment(DocBlock $docblock)
    {
        $indent = str_repeat($this->indentString, $this->indent);
        $firstIndent = $this->isFirstLineIndented ? $indent : '';
        // 3 === strlen(' * ')
        $wrapLength = $this->lineLength ? $this->lineLength - strlen($indent) - 3 : null;

        $text = $this->removeTrailingSpaces(
            $indent,
            $this->addAsterisksForEachLine(
                $indent,
                $this->getSummaryAndDescriptionTextBlock($docblock, $wrapLength)
            )
        );

        $comment = "{$firstIndent}';

        return $comment;
    }

    
    private function removeTrailingSpaces($indent, $text)
    {
        return str_replace("\n{$indent} * \n", "\n{$indent} *\n", $text);
    }

    
    private function addAsterisksForEachLine($indent, $text)
    {
        return str_replace("\n", "\n{$indent} * ", $text);
    }

    
    private function getSummaryAndDescriptionTextBlock(DocBlock $docblock, $wrapLength)
    {
        $text = $docblock->getSummary() . ((string)$docblock->getDescription() ? "\n\n" . $docblock->getDescription()
                : '');
        if ($wrapLength !== null) {
            $text = wordwrap($text, $wrapLength);
            return $text;
        }
        return $text;
    }

    
    private function addTagBlock(DocBlock $docblock, $wrapLength, $indent, $comment)
    {
        foreach ($docblock->getTags() as $tag) {
            $formatter = new DocBlock\Tags\Formatter\PassthroughFormatter();
            $tagText   = $formatter->format($tag);
            if ($wrapLength !== null) {
                $tagText = wordwrap($tagText, $wrapLength);
            }
            $tagText = str_replace("\n", "\n{$indent} * ", $tagText);

            $comment .= "{$indent} * {$tagText}\n";
        }

        return $comment;
    }
}
