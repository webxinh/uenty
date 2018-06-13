<?php

namespace Project\Command;

use \Symfony\Component\Console\Command\Command;
use \Codeception\CustomCommandInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

class MyCustomCommand extends Command implements CustomCommandInterface
{

    use \Codeception\Command\Shared\FileSystem;
    use \Codeception\Command\Shared\Config;

    
    public static function getCommandName()
    {
        return "myProject:myCommand";
    }

    
    protected function configure()
    {
        $this->setDefinition(array(
            new InputOption('friendly', 'f', InputOption::VALUE_NONE, 'The Message will be friendly'),
        ));

        parent::configure();
    }

    
    public function getDescription()
    {
        return "This is my command to say hello";
    }

    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $messageEnd = "!" . PHP_EOL;

        if ($input->getOption('friendly')) {
            $messageEnd = "," . PHP_EOL;
            $messageEnd .= "how are you?" . PHP_EOL;
        }

        echo "Hello " . get_current_user();
        echo $messageEnd . PHP_EOL;
    }
}
