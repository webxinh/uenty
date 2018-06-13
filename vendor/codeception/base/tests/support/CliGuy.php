<?php



class CliGuy extends \Codeception\Actor
{
    use _generated\CliGuyActions;

    
    public function seeInSupportDir($file)
    {
        $this->seeFileFound($file, 'tests/support');
    }
}
