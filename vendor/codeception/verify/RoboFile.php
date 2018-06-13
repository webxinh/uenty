<?php

class RoboFile extends \Robo\Tasks
{
    
    public function release($newVer = null)
    {
        if ($newVer) {
            $this->say("version updated to $newVer");
            $this->taskWriteToFile(__DIR__.'/VERSION')
                ->line($newVer)
                ->run();
        }
        $version = trim(file_get_contents(__DIR__.'/VERSION'));
        $this->taskGitStack()
            ->tag($version)
            ->push('origin','master --tags')
            ->run();

        $this->taskGitHubRelease($version)
            ->uri('Codeception/Verify')
            ->askForChanges()
            ->run();
    }
}