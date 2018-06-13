<?php


namespace phpDocumentor\Reflection\DocBlock\Tags;

use phpDocumentor\Reflection\DocBlock\Tag;


final class Example extends BaseTag
{
    
    private $filePath = '';

    
    private $isURI = false;

    
    public function getContent()
    {
        if (null === $this->description) {
            $filePath = '"' . $this->filePath . '"';
            if ($this->isURI) {
                $filePath = $this->isUriRelative($this->filePath)
                    ? str_replace('%2F', '/', rawurlencode($this->filePath))
                    :$this->filePath;
            }

            $this->description = $filePath . ' ' . parent::getContent();
        }

        return $this->description;
    }

    
    public static function create($body)
    {
        // File component: File path in quotes or File URI / Source information
        if (! preg_match('/^(?:\"([^\"]+)\"|(\S+))(?:\s+(.*))?$/sux', $body, $matches)) {
            return null;
        }

        $filePath = null;
        $fileUri  = null;
        if ('' !== $matches[1]) {
            $filePath = $matches[1];
        } else {
            $fileUri = $matches[2];
        }

        $startingLine = 1;
        $lineCount    = null;
        $description  = null;

        // Starting line / Number of lines / Description
        if (preg_match('/^([1-9]\d*)\s*(?:((?1))\s+)?(.*)$/sux', $matches[3], $matches)) {
            $startingLine = (int)$matches[1];
            if (isset($matches[2]) && $matches[2] !== '') {
                $lineCount = (int)$matches[2];
            }
            $description = $matches[3];
        }

        return new static($filePath, $fileUri, $startingLine, $lineCount, $description);
    }

    
    public function getFilePath()
    {
        return $this->filePath;
    }

    
    public function setFilePath($filePath)
    {
        $this->isURI = false;
        $this->filePath = trim($filePath);

        $this->description = null;
        return $this;
    }

    
    public function setFileURI($uri)
    {
        $this->isURI   = true;
        $this->description = null;

        $this->filePath = $this->isUriRelative($uri)
            ? rawurldecode(str_replace(array('/', '\\'), '%2F', $uri))
            : $this->filePath = $uri;

        return $this;
    }

    
    public function __toString()
    {
        return $this->filePath . ($this->description ? ' ' . $this->description->render() : '');
    }

    
    private function isUriRelative($uri)
    {
        return false === strpos($uri, ':');
    }
}
