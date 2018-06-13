<?php
namespace Codeception\Module;

use Codeception\Util\FileSystem as Util;
use Symfony\Component\Finder\Finder;
use Codeception\Module as CodeceptionModule;
use Codeception\TestInterface;
use Codeception\Configuration;


class Filesystem extends CodeceptionModule
{
    protected $file = null;
    protected $filepath = null;

    protected $path = '';

    public function _before(TestInterface $test)
    {
        $this->path = Configuration::projectDir();
    }

    
    public function amInPath($path)
    {
        chdir($this->path = $this->absolutizePath($path) . DIRECTORY_SEPARATOR);
        $this->debug('Moved to ' . getcwd());
    }

    
    protected function absolutizePath($path)
    {
        // *nix way
        if (strpos($path, '/') === 0) {
            return $path;
        }
        // windows
        if (strpos($path, ':\\') === 1) {
            return $path;
        }

        return $this->path . $path;
    }

    
    public function openFile($filename)
    {
        $this->file = file_get_contents($this->absolutizePath($filename));
        $this->filepath = $filename;
    }

    
    public function deleteFile($filename)
    {
        if (!file_exists($this->absolutizePath($filename))) {
            \PHPUnit_Framework_Assert::fail('file not found');
        }
        unlink($this->absolutizePath($filename));
    }

    
    public function deleteDir($dirname)
    {
        $dir = $this->absolutizePath($dirname);
        Util::deleteDir($dir);
    }

    
    public function copyDir($src, $dst)
    {
        Util::copyDir($src, $dst);
    }

    
    public function seeInThisFile($text)
    {
        $this->assertContains($text, $this->file, "No text '$text' in currently opened file");
    }

    
    public function seeNumberNewLines($number)
    {
        $lines = preg_split('/\n|\r/', $this->file);

        $this->assertTrue(
            (int) $number === count($lines),
            "The number of new lines does not match with $number"
        );
    }
    
    public function seeThisFileMatches($regex)
    {
        $this->assertRegExp($regex, $this->file, "Contents of currently opened file does not match '$regex'");
    }

    
    public function seeFileContentsEqual($text)
    {
        $file = str_replace("\r", '', $this->file);
        \PHPUnit_Framework_Assert::assertEquals($text, $file);
    }

    
    public function dontSeeInThisFile($text)
    {
        $this->assertNotContains($text, $this->file, "Found text '$text' in currently opened file");
    }

    
    public function deleteThisFile()
    {
        $this->deleteFile($this->filepath);
    }

    
    public function seeFileFound($filename, $path = '')
    {
        if ($path === '' && file_exists($filename)) {
            $this->openFile($filename);
            \PHPUnit_Framework_Assert::assertFileExists($filename);
            return;
        }

        $found = $this->findFileInPath($filename, $path);

        if ($found === false) {
            $this->fail("File \"$filename\" not found at \"$path\"");
        }

        $this->openFile($found);
        \PHPUnit_Framework_Assert::assertFileExists($found);
    }

    
    public function dontSeeFileFound($filename, $path = '')
    {
        if ($path === '') {
            \PHPUnit_Framework_Assert::assertFileNotExists($filename);
            return;
        }

        $found = $this->findFileInPath($filename, $path);

        if ($found === false) {
            //this line keeps a count of assertions correct
            \PHPUnit_Framework_Assert::assertTrue(true);
            return;
        }

        \PHPUnit_Framework_Assert::assertFileNotExists($found);
    }

    
    private function findFileInPath($filename, $path)
    {
        $path = $this->absolutizePath($path);
        if (!file_exists($path)) {
            $this->fail("Directory does not exist: $path");
        }

        $files = Finder::create()->files()->name($filename)->in($path);
        if ($files->count() === 0) {
            return false;
        }

        foreach ($files as $file) {
            return $file->getRealPath();
        }
    }


    
    public function cleanDir($dirname)
    {
        $path = $this->absolutizePath($dirname);
        Util::doEmptyDir($path);
    }

    
    public function writeToFile($filename, $contents)
    {
        file_put_contents($filename, $contents);
    }
}
