<?php



class CoverGuy extends \Codeception\Actor
{
    use _generated\CoverGuyActions;

   
    public function seeCoverageStatsNotEmpty()
    {
        $this->seeInShellOutput(
            <<<EOF
index
  Methods:  50.00% ( 1/ 2)   Lines:  50.00% (  2/  4)
EOF
        );
        $this->seeInShellOutput(
            <<<EOF
info
  Methods: 100.00% ( 1/ 1)   Lines: 100.00% (  4/  4)
EOF
        );
    }
}
