<?php
namespace Codeception\Module;

use Codeception\Module as CodeceptionModule;


class Cli extends CodeceptionModule
{
    public $output = '';

    public function _cleanup()
    {
        $this->output = '';
    }

    
    public function runShellCommand($command, $failNonZero = true)
    {
        $data = [];
        exec("$command", $data, $resultCode);
        $this->output = implode("\n", $data);
        if ($this->output === null) {
            \PHPUnit_Framework_Assert::fail("$command can't be executed");
        }
        if ($resultCode !== 0 && $failNonZero) {
            \PHPUnit_Framework_Assert::fail("Result code was $resultCode.\n\n" . $this->output);
        }
        $this->debug(preg_replace('~s/\e\[\d+(?>(;\d+)*)m//g~', '', $this->output));
    }

    
    public function seeInShellOutput($text)
    {
        \PHPUnit_Framework_Assert::assertContains($text, $this->output);
    }

    
    public function dontSeeInShellOutput($text)
    {
        $this->debug($this->output);
        \PHPUnit_Framework_Assert::assertNotContains($text, $this->output);
    }

    public function seeShellOutputMatches($regex)
    {
        \PHPUnit_Framework_Assert::assertRegExp($regex, $this->output);
    }
}
