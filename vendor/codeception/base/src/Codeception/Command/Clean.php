<?php
namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Util\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class Clean extends Command
{
    use Shared\Config;

    public function getDescription()
    {
        return 'Cleans or creates _output directory';
    }

    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getGlobalConfig($input->getOption('config'));
        $output->writeln("<info>Cleaning up " . Configuration::outputDir() . "...</info>");
        FileSystem::doEmptyDir(Configuration::outputDir());
        $output->writeln("Done");
    }
}
