<?php


namespace aabc\console;

use aabc\console\controllers\HelpController;


class UnknownCommandException extends Exception
{
    
    public $command;

    
    protected $application;


    
    public function __construct($route, $application, $code = 0, \Exception $previous = null)
    {
        $this->command = $route;
        $this->application = $application;
        parent::__construct("Unknown command \"$route\".", $code, $previous);
    }

    
    public function getName()
    {
        return 'Unknown command';
    }

    
    public function getSuggestedAlternatives()
    {
        $help = $this->application->createController('help');
        if ($help === false) {
            return [];
        }
        
        list($helpController, $actionID) = $help;

        $availableActions = [];
        $commands = $helpController->getCommands();
        foreach ($commands as $command) {
            $result = $this->application->createController($command);
            if ($result === false) {
                continue;
            }
            // add the command itself (default action)
            $availableActions[] = $command;

            // add all actions of this controller
            
            list($controller, $actionID) = $result;
            $actions = $helpController->getActions($controller);
            if (!empty($actions)) {
                $prefix = $controller->getUniqueId();
                foreach ($actions as $action) {
                    $availableActions[] = $prefix . '/' . $action;
                }
            }
        }
        return $this->filterBySimilarity($availableActions, $this->command);
    }

    
    private function filterBySimilarity($actions, $command)
    {
        $alternatives = [];

        // suggest alternatives that begin with $command first
        foreach ($actions as $action) {
            if (strpos($action, $command) === 0) {
                $alternatives[] = $action;
            }
        }

        // calculate the Levenshtein distance between the unknown command and all available commands.
        $distances = array_map(function($action) use ($command) {
            $action = strlen($action) > 255 ? substr($action, 0, 255) : $action;
            $command = strlen($command) > 255 ? substr($command, 0, 255) : $command;
            return levenshtein($action, $command);
        }, array_combine($actions, $actions));

        // we assume a typo if the levensthein distance is no more than 3, i.e. 3 replacements needed
        $relevantTypos = array_filter($distances, function($distance) {
            return $distance <= 3;
        });
        asort($relevantTypos);
        $alternatives = array_merge($alternatives, array_flip($relevantTypos));

        return array_unique($alternatives);
    }
}
