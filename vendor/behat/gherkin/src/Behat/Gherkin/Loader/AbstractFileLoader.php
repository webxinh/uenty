<?php

/*
 * This file is part of the Behat Gherkin.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Gherkin\Loader;


abstract class AbstractFileLoader implements FileLoaderInterface
{
    protected $basePath;

    
    public function setBasePath($path)
    {
        $this->basePath = realpath($path);
    }

    
    protected function findRelativePath($path)
    {
        if (null !== $this->basePath) {
            return strtr($path, array($this->basePath . DIRECTORY_SEPARATOR => ''));
        }

        return $path;
    }

    
    protected function findAbsolutePath($path)
    {
        if (is_file($path) || is_dir($path)) {
            return realpath($path);
        }

        if (null === $this->basePath) {
            return false;
        }

        if (is_file($this->basePath . DIRECTORY_SEPARATOR . $path)
               || is_dir($this->basePath . DIRECTORY_SEPARATOR . $path)) {
            return realpath($this->basePath . DIRECTORY_SEPARATOR . $path);
        }

        return false;
    }
}
