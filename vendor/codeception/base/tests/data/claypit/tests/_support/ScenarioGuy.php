<?php



class ScenarioGuy extends \Codeception\Actor
{
    use _generated\ScenarioGuyActions;

    public function seeCodeCoverageFilesArePresent()
    {
        $this->seeFileFound('c3.php');
        $this->seeFileFound('composer.json');
        $this->seeInThisFile('codeception/c3');
    }

    
    public function terminal()
    {
        $this->comment('I am terminal user!');
    }

    
    public function openCurrentDir()
    {
        $this->amInPath('.');
    }

    
    public function openDir($path)
    {
        $this->amInPath($path);
    }

    
    public function matchFile($name)
    {
        $this->seeFileFound($name);
    }

    
    public function thereAreValues($file, \Behat\Gherkin\Node\TableNode $node)
    {
        $this->seeFileFound($file);
        foreach ($node->getRows() as $row) {
            $this->seeThisFileMatches('~' . implode('.*?', $row) . '~');
        }
    }
}
