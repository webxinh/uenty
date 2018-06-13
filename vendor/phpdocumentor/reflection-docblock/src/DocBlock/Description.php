<?php


namespace phpDocumentor\Reflection\DocBlock;

use phpDocumentor\Reflection\DocBlock\Tags\Formatter;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter\PassthroughFormatter;
use Webmozart\Assert\Assert;


class Description
{
    
    private $bodyTemplate;

    
    private $tags;

    
    public function __construct($bodyTemplate, array $tags = [])
    {
        Assert::string($bodyTemplate);

        $this->bodyTemplate = $bodyTemplate;
        $this->tags = $tags;
    }

    
    public function render(Formatter $formatter = null)
    {
        if ($formatter === null) {
            $formatter = new PassthroughFormatter();
        }

        $tags = [];
        foreach ($this->tags as $tag) {
            $tags[] = '{' . $formatter->format($tag) . '}';
        }
        return vsprintf($this->bodyTemplate, $tags);
    }

    
    public function __toString()
    {
        return $this->render();
    }
}
