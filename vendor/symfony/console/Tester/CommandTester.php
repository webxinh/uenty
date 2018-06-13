<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Tester;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class CommandTester
{
    private $command;
    private $input;
    private $output;
    private $inputs = array();
    private $statusCode;

    
    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    
    public function execute(array $input, array $options = array())
    {
        // set the command name automatically if the application requires
        // this argument and no command name was passed
        if (!isset($input['command'])
            && (null !== $application = $this->command->getApplication())
            && $application->getDefinition()->hasArgument('command')
        ) {
            $input = array_merge(array('command' => $this->command->getName()), $input);
        }

        $this->input = new ArrayInput($input);
        if ($this->inputs) {
            $this->input->setStream(self::createStream($this->inputs));
        }

        if (isset($options['interactive'])) {
            $this->input->setInteractive($options['interactive']);
        }

        $this->output = new StreamOutput(fopen('php://memory', 'w', false));
        if (isset($options['decorated'])) {
            $this->output->setDecorated($options['decorated']);
        }
        if (isset($options['verbosity'])) {
            $this->output->setVerbosity($options['verbosity']);
        }

        return $this->statusCode = $this->command->run($this->input, $this->output);
    }

    
    public function getDisplay($normalize = false)
    {
        rewind($this->output->getStream());

        $display = stream_get_contents($this->output->getStream());

        if ($normalize) {
            $display = str_replace(PHP_EOL, "\n", $display);
        }

        return $display;
    }

    
    public function getInput()
    {
        return $this->input;
    }

    
    public function getOutput()
    {
        return $this->output;
    }

    
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    
    public function setInputs(array $inputs)
    {
        $this->inputs = $inputs;

        return $this;
    }

    private static function createStream(array $inputs)
    {
        $stream = fopen('php://memory', 'r+', false);

        fputs($stream, implode(PHP_EOL, $inputs));
        rewind($stream);

        return $stream;
    }
}
