<?php
/*
 * This file is part of the File_Iterator package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class File_Iterator_Factory
{
    
    public function getFileIterator($paths, $suffixes = '', $prefixes = '', array $exclude = array())
    {
        if (is_string($paths)) {
            $paths = array($paths);
        }

        $paths   = $this->getPathsAfterResolvingWildcards($paths);
        $exclude = $this->getPathsAfterResolvingWildcards($exclude);

        if (is_string($prefixes)) {
            if ($prefixes != '') {
                $prefixes = array($prefixes);
            } else {
                $prefixes = array();
            }
        }

        if (is_string($suffixes)) {
            if ($suffixes != '') {
                $suffixes = array($suffixes);
            } else {
                $suffixes = array();
            }
        }

        $iterator = new AppendIterator;

        foreach ($paths as $path) {
            if (is_dir($path)) {
                $iterator->append(
                  new File_Iterator(
                    new RecursiveIteratorIterator(
                      new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::FOLLOW_SYMLINKS)
                    ),
                    $suffixes,
                    $prefixes,
                    $exclude,
                    $path
                  )
                );
            }
        }

        return $iterator;
    }

    
    protected function getPathsAfterResolvingWildcards(array $paths)
    {
        $_paths = array();

        foreach ($paths as $path) {
            if ($locals = glob($path, GLOB_ONLYDIR)) {
                $_paths = array_merge($_paths, $locals);
            } else {
                $_paths[] = $path;
            }
        }

        return $_paths;
    }
}
