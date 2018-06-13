<?php


namespace phpDocumentor\Reflection;

use phpDocumentor\Reflection\DocBlock\Tags\Example;


class ExampleFinder
{
    
    private $sourceDirectory = '';

    
    private $exampleDirectories = array();

    
    public function find(Example $example)
    {
        $filename = $example->getFilePath();

        $file = $this->getExampleFileContents($filename);
        if (!$file) {
            return "** File not found : {$filename} **";
        }

        return implode('', array_slice($file, $example->getStartingLine() - 1, $example->getLineCount()));
    }

    
    public function setSourceDirectory($directory = '')
    {
        $this->sourceDirectory = $directory;
    }

    
    public function getSourceDirectory()
    {
        return $this->sourceDirectory;
    }

    
    public function setExampleDirectories(array $directories)
    {
        $this->exampleDirectories = $directories;
    }

    
    public function getExampleDirectories()
    {
        return $this->exampleDirectories;
    }

    
    private function getExampleFileContents($filename)
    {
        $normalizedPath = null;

        foreach ($this->exampleDirectories as $directory) {
            $exampleFileFromConfig = $this->constructExamplePath($directory, $filename);
            if (is_readable($exampleFileFromConfig)) {
                $normalizedPath = $exampleFileFromConfig;
                break;
            }
        }

        if (!$normalizedPath) {
            if (is_readable($this->getExamplePathFromSource($filename))) {
                $normalizedPath = $this->getExamplePathFromSource($filename);
            } elseif (is_readable($this->getExamplePathFromExampleDirectory($filename))) {
                $normalizedPath = $this->getExamplePathFromExampleDirectory($filename);
            } elseif (is_readable($filename)) {
                $normalizedPath = $filename;
            }
        }

        return $normalizedPath && is_readable($normalizedPath) ? file($normalizedPath) : null;
    }

    
    private function getExamplePathFromExampleDirectory($file)
    {
        return getcwd() . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . $file;
    }

    
    private function constructExamplePath($directory, $file)
    {
        return rtrim($directory, '\\/') . DIRECTORY_SEPARATOR . $file;
    }

    
    private function getExamplePathFromSource($file)
    {
        return sprintf(
            '%s%s%s',
            trim($this->getSourceDirectory(), '\\/'),
            DIRECTORY_SEPARATOR,
            trim($file, '"')
        );
    }
}
