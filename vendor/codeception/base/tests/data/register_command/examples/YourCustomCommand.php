<?php

namespace Project\Command;

use \Symfony\Component\Console\Command\Command;
use \Codeception\CustomCommandInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

class YourCustomCommand extends Command implements CustomCommandInterface
{

    use \Codeception\Command\Shared\FileSystem;
    use \Codeception\Command\Shared\Config;

    
    public static function getCommandName()
    {
        return "myProject:yourCommand";
    }

    
    protected function configure()
    {
        $this->setDefinition(array(
            new InputOption('something', 's', InputOption::VALUE_NONE, 'The Message will show you something more'),
        ));

        parent::configure();
    }

    
    public function getDescription()
    {
        return "This is your command make something";
    }

    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $messageEnd = "!" . PHP_EOL;

        if ($input->getOption('something')) {
            $messageEnd = "," . PHP_EOL;
            $messageEnd .= "push the Button!" . PHP_EOL;
        }

        echo "Hello Rabbit";
        echo $messageEnd . PHP_EOL;
    }
}
