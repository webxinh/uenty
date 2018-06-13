<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Command;

use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;


class Command
{
    private $application;
    private $name;
    private $processTitle;
    private $aliases = array();
    private $definition;
    private $hidden = false;
    private $help;
    private $description;
    private $ignoreValidationErrors = false;
    private $applicationDefinitionMerged = false;
    private $applicationDefinitionMergedWithArgs = false;
    private $code;
    private $synopsis = array();
    private $usages = array();
    private $helperSet;

    
    public function __construct($name = null)
    {
        $this->definition = new InputDefinition();

        if (null !== $name) {
            $this->setName($name);
        }

        $this->configure();

        if (!$this->name) {
            throw new LogicException(sprintf('The command defined in "%s" cannot have an empty name.', get_class($this)));
        }
    }

    
    public function ignoreValidationErrors()
    {
        $this->ignoreValidationErrors = true;
    }

    
    public function setApplication(Application $application = null)
    {
        $this->application = $application;
        if ($application) {
            $this->setHelperSet($application->getHelperSet());
        } else {
            $this->helperSet = null;
        }
    }

    
    public function setHelperSet(HelperSet $helperSet)
    {
        $this->helperSet = $helperSet;
    }

    
    public function getHelperSet()
    {
        return $this->helperSet;
    }

    
    public function getApplication()
    {
        return $this->application;
    }

    
    public function isEnabled()
    {
        return true;
    }

    
    protected function configure()
    {
    }

    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        throw new LogicException('You must override the execute() method in the concrete command class.');
    }

    
    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }

    
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
    }

    
    public function run(InputInterface $input, OutputInterface $output)
    {
        // force the creation of the synopsis before the merge with the app definition
        $this->getSynopsis(true);
        $this->getSynopsis(false);

        // add the application arguments and options
        $this->mergeApplicationDefinition();

        // bind the input against the command specific arguments/options
        try {
            $input->bind($this->definition);
        } catch (ExceptionInterface $e) {
            if (!$this->ignoreValidationErrors) {
                throw $e;
            }
        }

        $this->initialize($input, $output);

        if (null !== $this->processTitle) {
            if (function_exists('cli_set_process_title')) {
                if (false === @cli_set_process_title($this->processTitle)) {
                    if ('Darwin' === PHP_OS) {
                        $output->writeln('<comment>Running "cli_get_process_title" as an unprivileged user is not supported on MacOS.</comment>');
                    } else {
                        $error = error_get_last();
                        trigger_error($error['message'], E_USER_WARNING);
                    }
                }
            } elseif (function_exists('setproctitle')) {
                setproctitle($this->processTitle);
            } elseif (OutputInterface::VERBOSITY_VERY_VERBOSE === $output->getVerbosity()) {
                $output->writeln('<comment>Install the proctitle PECL to be able to change the process title.</comment>');
            }
        }

        if ($input->isInteractive()) {
            $this->interact($input, $output);
        }

        // The command name argument is often omitted when a command is executed directly with its run() method.
        // It would fail the validation if we didn't make sure the command argument is present,
        // since it's required by the application.
        if ($input->hasArgument('command') && null === $input->getArgument('command')) {
            $input->setArgument('command', $this->getName());
        }

        $input->validate();

        if ($this->code) {
            $statusCode = call_user_func($this->code, $input, $output);
        } else {
            $statusCode = $this->execute($input, $output);
        }

        return is_numeric($statusCode) ? (int) $statusCode : 0;
    }

    
    public function setCode(callable $code)
    {
        if ($code instanceof \Closure) {
            $r = new \ReflectionFunction($code);
            if (null === $r->getClosureThis()) {
                if (PHP_VERSION_ID < 70000) {
                    // Bug in PHP5: https://bugs.php.net/bug.php?id=64761
                    // This means that we cannot bind static closures and therefore we must
                    // ignore any errors here.  There is no way to test if the closure is
                    // bindable.
                    $code = @\Closure::bind($code, $this);
                } else {
                    $code = \Closure::bind($code, $this);
                }
            }
        }

        $this->code = $code;

        return $this;
    }

    
    public function mergeApplicationDefinition($mergeArgs = true)
    {
        if (null === $this->application || (true === $this->applicationDefinitionMerged && ($this->applicationDefinitionMergedWithArgs || !$mergeArgs))) {
            return;
        }

        $this->definition->addOptions($this->application->getDefinition()->getOptions());

        if ($mergeArgs) {
            $currentArguments = $this->definition->getArguments();
            $this->definition->setArguments($this->application->getDefinition()->getArguments());
            $this->definition->addArguments($currentArguments);
        }

        $this->applicationDefinitionMerged = true;
        if ($mergeArgs) {
            $this->applicationDefinitionMergedWithArgs = true;
        }
    }

    
    public function setDefinition($definition)
    {
        if ($definition instanceof InputDefinition) {
            $this->definition = $definition;
        } else {
            $this->definition->setDefinition($definition);
        }

        $this->applicationDefinitionMerged = false;

        return $this;
    }

    
    public function getDefinition()
    {
        return $this->definition;
    }

    
    public function getNativeDefinition()
    {
        return $this->getDefinition();
    }

    
    public function addArgument($name, $mode = null, $description = '', $default = null)
    {
        $this->definition->addArgument(new InputArgument($name, $mode, $description, $default));

        return $this;
    }

    
    public function addOption($name, $shortcut = null, $mode = null, $description = '', $default = null)
    {
        $this->definition->addOption(new InputOption($name, $shortcut, $mode, $description, $default));

        return $this;
    }

    
    public function setName($name)
    {
        $this->validateName($name);

        $this->name = $name;

        return $this;
    }

    
    public function setProcessTitle($title)
    {
        $this->processTitle = $title;

        return $this;
    }

    
    public function getName()
    {
        return $this->name;
    }

    
    public function setHidden($hidden)
    {
        $this->hidden = (bool) $hidden;

        return $this;
    }

    
    public function isHidden()
    {
        return $this->hidden;
    }

    
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    
    public function getDescription()
    {
        return $this->description;
    }

    
    public function setHelp($help)
    {
        $this->help = $help;

        return $this;
    }

    
    public function getHelp()
    {
        return $this->help;
    }

    
    public function getProcessedHelp()
    {
        $name = $this->name;

        $placeholders = array(
            '%command.name%',
            '%command.full_name%',
        );
        $replacements = array(
            $name,
            $_SERVER['PHP_SELF'].' '.$name,
        );

        return str_replace($placeholders, $replacements, $this->getHelp() ?: $this->getDescription());
    }

    
    public function setAliases($aliases)
    {
        if (!is_array($aliases) && !$aliases instanceof \Traversable) {
            throw new InvalidArgumentException('$aliases must be an array or an instance of \Traversable');
        }

        foreach ($aliases as $alias) {
            $this->validateName($alias);
        }

        $this->aliases = $aliases;

        return $this;
    }

    
    public function getAliases()
    {
        return $this->aliases;
    }

    
    public function getSynopsis($short = false)
    {
        $key = $short ? 'short' : 'long';

        if (!isset($this->synopsis[$key])) {
            $this->synopsis[$key] = trim(sprintf('%s %s', $this->name, $this->definition->getSynopsis($short)));
        }

        return $this->synopsis[$key];
    }

    
    public function addUsage($usage)
    {
        if (0 !== strpos($usage, $this->name)) {
            $usage = sprintf('%s %s', $this->name, $usage);
        }

        $this->usages[] = $usage;

        return $this;
    }

    
    public function getUsages()
    {
        return $this->usages;
    }

    
    public function getHelper($name)
    {
        if (null === $this->helperSet) {
            throw new LogicException(sprintf('Cannot retrieve helper "%s" because there is no HelperSet defined. Did you forget to add your command to the application or to set the application on the command using the setApplication() method? You can also set the HelperSet directly using the setHelperSet() method.', $name));
        }

        return $this->helperSet->get($name);
    }

    
    private function validateName($name)
    {
        if (!preg_match('/^[^\:]++(\:[^\:]++)*$/', $name)) {
            throw new InvalidArgumentException(sprintf('Command name "%s" is invalid.', $name));
        }
    }
}
