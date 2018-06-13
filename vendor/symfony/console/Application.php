<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console;

use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputAwareInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class Application
{
    private $commands = array();
    private $wantHelps = false;
    private $runningCommand;
    private $name;
    private $version;
    private $catchExceptions = true;
    private $autoExit = true;
    private $definition;
    private $helperSet;
    private $dispatcher;
    private $terminal;
    private $defaultCommand;
    private $singleCommand;

    
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->name = $name;
        $this->version = $version;
        $this->terminal = new Terminal();
        $this->defaultCommand = 'list';
        $this->helperSet = $this->getDefaultHelperSet();
        $this->definition = $this->getDefaultInputDefinition();

        foreach ($this->getDefaultCommands() as $command) {
            $this->add($command);
        }
    }

    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        putenv('LINES='.$this->terminal->getHeight());
        putenv('COLUMNS='.$this->terminal->getWidth());

        if (null === $input) {
            $input = new ArgvInput();
        }

        if (null === $output) {
            $output = new ConsoleOutput();
        }

        $this->configureIO($input, $output);

        try {
            $exitCode = $this->doRun($input, $output);
        } catch (\Exception $e) {
            if (!$this->catchExceptions) {
                throw $e;
            }

            if ($output instanceof ConsoleOutputInterface) {
                $this->renderException($e, $output->getErrorOutput());
            } else {
                $this->renderException($e, $output);
            }

            $exitCode = $e->getCode();
            if (is_numeric($exitCode)) {
                $exitCode = (int) $exitCode;
                if (0 === $exitCode) {
                    $exitCode = 1;
                }
            } else {
                $exitCode = 1;
            }
        }

        if ($this->autoExit) {
            if ($exitCode > 255) {
                $exitCode = 255;
            }

            exit($exitCode);
        }

        return $exitCode;
    }

    
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if (true === $input->hasParameterOption(array('--version', '-V'), true)) {
            $output->writeln($this->getLongVersion());

            return 0;
        }

        $name = $this->getCommandName($input);
        if (true === $input->hasParameterOption(array('--help', '-h'), true)) {
            if (!$name) {
                $name = 'help';
                $input = new ArrayInput(array('command_name' => $this->defaultCommand));
            } else {
                $this->wantHelps = true;
            }
        }

        if (!$name) {
            $name = $this->defaultCommand;
            $input = new ArrayInput(array('command' => $this->defaultCommand));
        }

        // the command name MUST be the first element of the input
        $command = $this->find($name);

        $this->runningCommand = $command;
        $exitCode = $this->doRunCommand($command, $input, $output);
        $this->runningCommand = null;

        return $exitCode;
    }

    
    public function setHelperSet(HelperSet $helperSet)
    {
        $this->helperSet = $helperSet;
    }

    
    public function getHelperSet()
    {
        return $this->helperSet;
    }

    
    public function setDefinition(InputDefinition $definition)
    {
        $this->definition = $definition;
    }

    
    public function getDefinition()
    {
        if ($this->singleCommand) {
            $inputDefinition = $this->definition;
            $inputDefinition->setArguments();

            return $inputDefinition;
        }

        return $this->definition;
    }

    
    public function getHelp()
    {
        return $this->getLongVersion();
    }

    
    public function areExceptionsCaught()
    {
        return $this->catchExceptions;
    }

    
    public function setCatchExceptions($boolean)
    {
        $this->catchExceptions = (bool) $boolean;
    }

    
    public function isAutoExitEnabled()
    {
        return $this->autoExit;
    }

    
    public function setAutoExit($boolean)
    {
        $this->autoExit = (bool) $boolean;
    }

    
    public function getName()
    {
        return $this->name;
    }

    
    public function setName($name)
    {
        $this->name = $name;
    }

    
    public function getVersion()
    {
        return $this->version;
    }

    
    public function setVersion($version)
    {
        $this->version = $version;
    }

    
    public function getLongVersion()
    {
        if ('UNKNOWN' !== $this->getName()) {
            if ('UNKNOWN' !== $this->getVersion()) {
                return sprintf('%s <info>%s</info>', $this->getName(), $this->getVersion());
            }

            return $this->getName();
        }

        return 'Console Tool';
    }

    
    public function register($name)
    {
        return $this->add(new Command($name));
    }

    
    public function addCommands(array $commands)
    {
        foreach ($commands as $command) {
            $this->add($command);
        }
    }

    
    public function add(Command $command)
    {
        $command->setApplication($this);

        if (!$command->isEnabled()) {
            $command->setApplication(null);

            return;
        }

        if (null === $command->getDefinition()) {
            throw new LogicException(sprintf('Command class "%s" is not correctly initialized. You probably forgot to call the parent constructor.', get_class($command)));
        }

        $this->commands[$command->getName()] = $command;

        foreach ($command->getAliases() as $alias) {
            $this->commands[$alias] = $command;
        }

        return $command;
    }

    
    public function get($name)
    {
        if (!isset($this->commands[$name])) {
            throw new CommandNotFoundException(sprintf('The command "%s" does not exist.', $name));
        }

        $command = $this->commands[$name];

        if ($this->wantHelps) {
            $this->wantHelps = false;

            $helpCommand = $this->get('help');
            $helpCommand->setCommand($command);

            return $helpCommand;
        }

        return $command;
    }

    
    public function has($name)
    {
        return isset($this->commands[$name]);
    }

    
    public function getNamespaces()
    {
        $namespaces = array();
        foreach ($this->all() as $command) {
            $namespaces = array_merge($namespaces, $this->extractAllNamespaces($command->getName()));

            foreach ($command->getAliases() as $alias) {
                $namespaces = array_merge($namespaces, $this->extractAllNamespaces($alias));
            }
        }

        return array_values(array_unique(array_filter($namespaces)));
    }

    
    public function findNamespace($namespace)
    {
        $allNamespaces = $this->getNamespaces();
        $expr = preg_replace_callback('{([^:]+|)}', function ($matches) { return preg_quote($matches[1]).'[^:]*'; }, $namespace);
        $namespaces = preg_grep('{^'.$expr.'}', $allNamespaces);

        if (empty($namespaces)) {
            $message = sprintf('There are no commands defined in the "%s" namespace.', $namespace);

            if ($alternatives = $this->findAlternatives($namespace, $allNamespaces)) {
                if (1 == count($alternatives)) {
                    $message .= "\n\nDid you mean this?\n    ";
                } else {
                    $message .= "\n\nDid you mean one of these?\n    ";
                }

                $message .= implode("\n    ", $alternatives);
            }

            throw new CommandNotFoundException($message, $alternatives);
        }

        $exact = in_array($namespace, $namespaces, true);
        if (count($namespaces) > 1 && !$exact) {
            throw new CommandNotFoundException(sprintf('The namespace "%s" is ambiguous (%s).', $namespace, $this->getAbbreviationSuggestions(array_values($namespaces))), array_values($namespaces));
        }

        return $exact ? $namespace : reset($namespaces);
    }

    
    public function find($name)
    {
        $allCommands = array_keys($this->commands);
        $expr = preg_replace_callback('{([^:]+|)}', function ($matches) { return preg_quote($matches[1]).'[^:]*'; }, $name);
        $commands = preg_grep('{^'.$expr.'}', $allCommands);

        if (empty($commands) || count(preg_grep('{^'.$expr.'$}', $commands)) < 1) {
            if (false !== $pos = strrpos($name, ':')) {
                // check if a namespace exists and contains commands
                $this->findNamespace(substr($name, 0, $pos));
            }

            $message = sprintf('Command "%s" is not defined.', $name);

            if ($alternatives = $this->findAlternatives($name, $allCommands)) {
                if (1 == count($alternatives)) {
                    $message .= "\n\nDid you mean this?\n    ";
                } else {
                    $message .= "\n\nDid you mean one of these?\n    ";
                }
                $message .= implode("\n    ", $alternatives);
            }

            throw new CommandNotFoundException($message, $alternatives);
        }

        // filter out aliases for commands which are already on the list
        if (count($commands) > 1) {
            $commandList = $this->commands;
            $commands = array_filter($commands, function ($nameOrAlias) use ($commandList, $commands) {
                $commandName = $commandList[$nameOrAlias]->getName();

                return $commandName === $nameOrAlias || !in_array($commandName, $commands);
            });
        }

        $exact = in_array($name, $commands, true);
        if (count($commands) > 1 && !$exact) {
            $suggestions = $this->getAbbreviationSuggestions(array_values($commands));

            throw new CommandNotFoundException(sprintf('Command "%s" is ambiguous (%s).', $name, $suggestions), array_values($commands));
        }

        return $this->get($exact ? $name : reset($commands));
    }

    
    public function all($namespace = null)
    {
        if (null === $namespace) {
            return $this->commands;
        }

        $commands = array();
        foreach ($this->commands as $name => $command) {
            if ($namespace === $this->extractNamespace($name, substr_count($namespace, ':') + 1)) {
                $commands[$name] = $command;
            }
        }

        return $commands;
    }

    
    public static function getAbbreviations($names)
    {
        $abbrevs = array();
        foreach ($names as $name) {
            for ($len = strlen($name); $len > 0; --$len) {
                $abbrev = substr($name, 0, $len);
                $abbrevs[$abbrev][] = $name;
            }
        }

        return $abbrevs;
    }

    
    public function renderException(\Exception $e, OutputInterface $output)
    {
        $output->writeln('', OutputInterface::VERBOSITY_QUIET);

        do {
            $title = sprintf(
                '  [%s%s]  ',
                get_class($e),
                $output->isVerbose() && 0 !== ($code = $e->getCode()) ? ' ('.$code.')' : ''
            );

            $len = $this->stringWidth($title);

            $width = $this->terminal->getWidth() ? $this->terminal->getWidth() - 1 : PHP_INT_MAX;
            // HHVM only accepts 32 bits integer in str_split, even when PHP_INT_MAX is a 64 bit integer: https://github.com/facebook/hhvm/issues/1327
            if (defined('HHVM_VERSION') && $width > 1 << 31) {
                $width = 1 << 31;
            }
            $formatter = $output->getFormatter();
            $lines = array();
            foreach (preg_split('/\r?\n/', $e->getMessage()) as $line) {
                foreach ($this->splitStringByWidth($line, $width - 4) as $line) {
                    // pre-format lines to get the right string length
                    $lineLength = $this->stringWidth(preg_replace('/\[[^m]*m/', '', $formatter->format($line))) + 4;
                    $lines[] = array($line, $lineLength);

                    $len = max($lineLength, $len);
                }
            }

            $messages = array();
            $messages[] = $emptyLine = $formatter->format(sprintf('<error>%s</error>', str_repeat(' ', $len)));
            $messages[] = $formatter->format(sprintf('<error>%s%s</error>', $title, str_repeat(' ', max(0, $len - $this->stringWidth($title)))));
            foreach ($lines as $line) {
                $messages[] = $formatter->format(sprintf('<error>  %s  %s</error>', $line[0], str_repeat(' ', $len - $line[1])));
            }
            $messages[] = $emptyLine;
            $messages[] = '';

            $output->writeln($messages, OutputInterface::OUTPUT_RAW | OutputInterface::VERBOSITY_QUIET);

            if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
                $output->writeln('<comment>Exception trace:</comment>', OutputInterface::VERBOSITY_QUIET);

                // exception related properties
                $trace = $e->getTrace();
                array_unshift($trace, array(
                    'function' => '',
                    'file' => $e->getFile() !== null ? $e->getFile() : 'n/a',
                    'line' => $e->getLine() !== null ? $e->getLine() : 'n/a',
                    'args' => array(),
                ));

                for ($i = 0, $count = count($trace); $i < $count; ++$i) {
                    $class = isset($trace[$i]['class']) ? $trace[$i]['class'] : '';
                    $type = isset($trace[$i]['type']) ? $trace[$i]['type'] : '';
                    $function = $trace[$i]['function'];
                    $file = isset($trace[$i]['file']) ? $trace[$i]['file'] : 'n/a';
                    $line = isset($trace[$i]['line']) ? $trace[$i]['line'] : 'n/a';

                    $output->writeln(sprintf(' %s%s%s() at <info>%s:%s</info>', $class, $type, $function, $file, $line), OutputInterface::VERBOSITY_QUIET);
                }

                $output->writeln('', OutputInterface::VERBOSITY_QUIET);
            }
        } while ($e = $e->getPrevious());

        if (null !== $this->runningCommand) {
            $output->writeln(sprintf('<info>%s</info>', sprintf($this->runningCommand->getSynopsis(), $this->getName())), OutputInterface::VERBOSITY_QUIET);
            $output->writeln('', OutputInterface::VERBOSITY_QUIET);
        }
    }

    
    protected function getTerminalWidth()
    {
        @trigger_error(sprintf('%s is deprecated as of 3.2 and will be removed in 4.0. Create a Terminal instance instead.', __METHOD__), E_USER_DEPRECATED);

        return $this->terminal->getWidth();
    }

    
    protected function getTerminalHeight()
    {
        @trigger_error(sprintf('%s is deprecated as of 3.2 and will be removed in 4.0. Create a Terminal instance instead.', __METHOD__), E_USER_DEPRECATED);

        return $this->terminal->getHeight();
    }

    
    public function getTerminalDimensions()
    {
        @trigger_error(sprintf('%s is deprecated as of 3.2 and will be removed in 4.0. Create a Terminal instance instead.', __METHOD__), E_USER_DEPRECATED);

        return array($this->terminal->getWidth(), $this->terminal->getHeight());
    }

    
    public function setTerminalDimensions($width, $height)
    {
        @trigger_error(sprintf('%s is deprecated as of 3.2 and will be removed in 4.0. Set the COLUMNS and LINES env vars instead.', __METHOD__), E_USER_DEPRECATED);

        putenv('COLUMNS='.$width);
        putenv('LINES='.$height);

        return $this;
    }

    
    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        if (true === $input->hasParameterOption(array('--ansi'), true)) {
            $output->setDecorated(true);
        } elseif (true === $input->hasParameterOption(array('--no-ansi'), true)) {
            $output->setDecorated(false);
        }

        if (true === $input->hasParameterOption(array('--no-interaction', '-n'), true)) {
            $input->setInteractive(false);
        } elseif (function_exists('posix_isatty')) {
            $inputStream = null;

            if ($input instanceof StreamableInputInterface) {
                $inputStream = $input->getStream();
            }

            // This check ensures that calling QuestionHelper::setInputStream() works
            // To be removed in 4.0 (in the same time as QuestionHelper::setInputStream)
            if (!$inputStream && $this->getHelperSet()->has('question')) {
                $inputStream = $this->getHelperSet()->get('question')->getInputStream(false);
            }

            if (!@posix_isatty($inputStream) && false === getenv('SHELL_INTERACTIVE')) {
                $input->setInteractive(false);
            }
        }

        if (true === $input->hasParameterOption(array('--quiet', '-q'), true)) {
            $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);
            $input->setInteractive(false);
        } else {
            if ($input->hasParameterOption('-vvv', true) || $input->hasParameterOption('--verbose=3', true) || $input->getParameterOption('--verbose', false, true) === 3) {
                $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
            } elseif ($input->hasParameterOption('-vv', true) || $input->hasParameterOption('--verbose=2', true) || $input->getParameterOption('--verbose', false, true) === 2) {
                $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
            } elseif ($input->hasParameterOption('-v', true) || $input->hasParameterOption('--verbose=1', true) || $input->hasParameterOption('--verbose', true) || $input->getParameterOption('--verbose', false, true)) {
                $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
            }
        }
    }

    
    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output)
    {
        foreach ($command->getHelperSet() as $helper) {
            if ($helper instanceof InputAwareInterface) {
                $helper->setInput($input);
            }
        }

        if (null === $this->dispatcher) {
            try {
                return $command->run($input, $output);
            } catch (\Exception $e) {
                throw $e;
            } catch (\Throwable $e) {
                throw new FatalThrowableError($e);
            }
        }

        // bind before the console.command event, so the listeners have access to input options/arguments
        try {
            $command->mergeApplicationDefinition();
            $input->bind($command->getDefinition());
        } catch (ExceptionInterface $e) {
            // ignore invalid options/arguments for now, to allow the event listeners to customize the InputDefinition
        }

        $event = new ConsoleCommandEvent($command, $input, $output);
        $this->dispatcher->dispatch(ConsoleEvents::COMMAND, $event);

        if ($event->commandShouldRun()) {
            try {
                $e = null;
                $exitCode = $command->run($input, $output);
            } catch (\Exception $x) {
                $e = $x;
            } catch (\Throwable $x) {
                $e = new FatalThrowableError($x);
            }
            if (null !== $e) {
                $event = new ConsoleExceptionEvent($command, $input, $output, $e, $e->getCode());
                $this->dispatcher->dispatch(ConsoleEvents::EXCEPTION, $event);

                if ($e !== $event->getException()) {
                    $x = $e = $event->getException();
                }

                $event = new ConsoleTerminateEvent($command, $input, $output, $e->getCode());
                $this->dispatcher->dispatch(ConsoleEvents::TERMINATE, $event);

                throw $x;
            }
        } else {
            $exitCode = ConsoleCommandEvent::RETURN_CODE_DISABLED;
        }

        $event = new ConsoleTerminateEvent($command, $input, $output, $exitCode);
        $this->dispatcher->dispatch(ConsoleEvents::TERMINATE, $event);

        return $event->getExitCode();
    }

    
    protected function getCommandName(InputInterface $input)
    {
        return $this->singleCommand ? $this->defaultCommand : $input->getFirstArgument();
    }

    
    protected function getDefaultInputDefinition()
    {
        return new InputDefinition(array(
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),

            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message'),
            new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Do not output any message'),
            new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this application version'),
            new InputOption('--ansi', '', InputOption::VALUE_NONE, 'Force ANSI output'),
            new InputOption('--no-ansi', '', InputOption::VALUE_NONE, 'Disable ANSI output'),
            new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Do not ask any interactive question'),
        ));
    }

    
    protected function getDefaultCommands()
    {
        return array(new HelpCommand(), new ListCommand());
    }

    
    protected function getDefaultHelperSet()
    {
        return new HelperSet(array(
            new FormatterHelper(),
            new DebugFormatterHelper(),
            new ProcessHelper(),
            new QuestionHelper(),
        ));
    }

    
    private function getAbbreviationSuggestions($abbrevs)
    {
        return sprintf('%s, %s%s', $abbrevs[0], $abbrevs[1], count($abbrevs) > 2 ? sprintf(' and %d more', count($abbrevs) - 2) : '');
    }

    
    public function extractNamespace($name, $limit = null)
    {
        $parts = explode(':', $name);
        array_pop($parts);

        return implode(':', null === $limit ? $parts : array_slice($parts, 0, $limit));
    }

    
    private function findAlternatives($name, $collection)
    {
        $threshold = 1e3;
        $alternatives = array();

        $collectionParts = array();
        foreach ($collection as $item) {
            $collectionParts[$item] = explode(':', $item);
        }

        foreach (explode(':', $name) as $i => $subname) {
            foreach ($collectionParts as $collectionName => $parts) {
                $exists = isset($alternatives[$collectionName]);
                if (!isset($parts[$i]) && $exists) {
                    $alternatives[$collectionName] += $threshold;
                    continue;
                } elseif (!isset($parts[$i])) {
                    continue;
                }

                $lev = levenshtein($subname, $parts[$i]);
                if ($lev <= strlen($subname) / 3 || '' !== $subname && false !== strpos($parts[$i], $subname)) {
                    $alternatives[$collectionName] = $exists ? $alternatives[$collectionName] + $lev : $lev;
                } elseif ($exists) {
                    $alternatives[$collectionName] += $threshold;
                }
            }
        }

        foreach ($collection as $item) {
            $lev = levenshtein($name, $item);
            if ($lev <= strlen($name) / 3 || false !== strpos($item, $name)) {
                $alternatives[$item] = isset($alternatives[$item]) ? $alternatives[$item] - $lev : $lev;
            }
        }

        $alternatives = array_filter($alternatives, function ($lev) use ($threshold) { return $lev < 2 * $threshold; });
        asort($alternatives);

        return array_keys($alternatives);
    }

    
    public function setDefaultCommand($commandName, $isSingleCommand = false)
    {
        $this->defaultCommand = $commandName;

        if ($isSingleCommand) {
            // Ensure the command exist
            $this->find($commandName);

            $this->singleCommand = true;
        }

        return $this;
    }

    private function stringWidth($string)
    {
        if (false === $encoding = mb_detect_encoding($string, null, true)) {
            return strlen($string);
        }

        return mb_strwidth($string, $encoding);
    }

    private function splitStringByWidth($string, $width)
    {
        // str_split is not suitable for multi-byte characters, we should use preg_split to get char array properly.
        // additionally, array_slice() is not enough as some character has doubled width.
        // we need a function to split string not by character count but by string width
        if (false === $encoding = mb_detect_encoding($string, null, true)) {
            return str_split($string, $width);
        }

        $utf8String = mb_convert_encoding($string, 'utf8', $encoding);
        $lines = array();
        $line = '';
        foreach (preg_split('//u', $utf8String) as $char) {
            // test if $char could be appended to current line
            if (mb_strwidth($line.$char, 'utf8') <= $width) {
                $line .= $char;
                continue;
            }
            // if not, push current line to array and make new line
            $lines[] = str_pad($line, $width);
            $line = $char;
        }
        if ('' !== $line) {
            $lines[] = count($lines) ? str_pad($line, $width) : $line;
        }

        mb_convert_variables($encoding, 'utf8', $lines);

        return $lines;
    }

    
    private function extractAllNamespaces($name)
    {
        // -1 as third argument is needed to skip the command short name when exploding
        $parts = explode(':', $name, -1);
        $namespaces = array();

        foreach ($parts as $part) {
            if (count($namespaces)) {
                $namespaces[] = end($namespaces).':'.$part;
            } else {
                $namespaces[] = $part;
            }
        }

        return $namespaces;
    }
}
