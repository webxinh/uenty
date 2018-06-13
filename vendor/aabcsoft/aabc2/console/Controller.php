<?php


namespace aabc\console;

use Aabc;
use aabc\base\Action;
use aabc\base\InlineAction;
use aabc\base\InvalidRouteException;
use aabc\helpers\Console;


class Controller extends \aabc\base\Controller
{
    const EXIT_CODE_NORMAL = 0;
    const EXIT_CODE_ERROR = 1;

    
    public $interactive = true;
    
    public $color;
    
    public $help;

    
    private $_passedOptions = [];


    
    public function isColorEnabled($stream = \STDOUT)
    {
        return $this->color === null ? Console::streamSupportsAnsiColors($stream) : $this->color;
    }

    
    public function runAction($id, $params = [])
    {
        if (!empty($params)) {
            // populate options here so that they are available in beforeAction().
            $options = $this->options($id === '' ? $this->defaultAction : $id);
            if (isset($params['_aliases'])) {
                $optionAliases = $this->optionAliases();
                foreach ($params['_aliases'] as $name => $value) {
                    if (array_key_exists($name, $optionAliases)) {
                        $params[$optionAliases[$name]] = $value;
                    } else {
                        throw new Exception(Aabc::t('aabc', 'Unknown alias: -{name}', ['name' => $name]));
                    }
                }
                unset($params['_aliases']);
            }
            foreach ($params as $name => $value) {
                if (in_array($name, $options, true)) {
                    $default = $this->$name;
                    if (is_array($default)) {
                        $this->$name = preg_split('/\s*,\s*(?![^()]*\))/', $value);
                    } elseif ($default !== null) {
                        settype($value, gettype($default));
                        $this->$name = $value;
                    } else {
                        $this->$name = $value;
                    }
                    $this->_passedOptions[] = $name;
                    unset($params[$name]);
                } elseif (!is_int($name)) {
                    throw new Exception(Aabc::t('aabc', 'Unknown option: --{name}', ['name' => $name]));
                }
            }
        }
        if ($this->help) {
            $route = $this->getUniqueId() . '/' . $id;
            return Aabc::$app->runAction('help', [$route]);
        }
        return parent::runAction($id, $params);
    }

    
    public function bindActionParams($action, $params)
    {
        if ($action instanceof InlineAction) {
            $method = new \ReflectionMethod($this, $action->actionMethod);
        } else {
            $method = new \ReflectionMethod($action, 'run');
        }

        $args = array_values($params);

        $missing = [];
        foreach ($method->getParameters() as $i => $param) {
            if ($param->isArray() && isset($args[$i])) {
                $args[$i] = preg_split('/\s*,\s*/', $args[$i]);
            }
            if (!isset($args[$i])) {
                if ($param->isDefaultValueAvailable()) {
                    $args[$i] = $param->getDefaultValue();
                } else {
                    $missing[] = $param->getName();
                }
            }
        }

        if (!empty($missing)) {
            throw new Exception(Aabc::t('aabc', 'Missing required arguments: {params}', ['params' => implode(', ', $missing)]));
        }

        return $args;
    }

    
    public function ansiFormat($string)
    {
        if ($this->isColorEnabled()) {
            $args = func_get_args();
            array_shift($args);
            $string = Console::ansiFormat($string, $args);
        }
        return $string;
    }

    
    public function stdout($string)
    {
        if ($this->isColorEnabled()) {
            $args = func_get_args();
            array_shift($args);
            $string = Console::ansiFormat($string, $args);
        }
        return Console::stdout($string);
    }

    
    public function stderr($string)
    {
        if ($this->isColorEnabled(\STDERR)) {
            $args = func_get_args();
            array_shift($args);
            $string = Console::ansiFormat($string, $args);
        }
        return fwrite(\STDERR, $string);
    }

    
    public function prompt($text, $options = [])
    {
        if ($this->interactive) {
            return Console::prompt($text, $options);
        }

        return isset($options['default']) ? $options['default'] : '';
    }

    
    public function confirm($message, $default = false)
    {
        if ($this->interactive) {
            return Console::confirm($message, $default);
        }

        return true;
    }

    
    public function select($prompt, $options = [])
    {
        return Console::select($prompt, $options);
    }

    
    public function options($actionID)
    {
        // $actionId might be used in subclasses to provide options specific to action id
        return ['color', 'interactive', 'help'];
    }

    
    public function optionAliases()
    {
        return [
            'h' => 'help'
        ];
    }

    
    public function getOptionValues($actionID)
    {
        // $actionId might be used in subclasses to provide properties specific to action id
        $properties = [];
        foreach ($this->options($this->action->id) as $property) {
            $properties[$property] = $this->$property;
        }
        return $properties;
    }

    
    public function getPassedOptions()
    {
        return $this->_passedOptions;
    }

    
    public function getPassedOptionValues()
    {
        $properties = [];
        foreach ($this->_passedOptions as $property) {
            $properties[$property] = $this->$property;
        }
        return $properties;
    }

    
    public function getHelpSummary()
    {
        return $this->parseDocCommentSummary(new \ReflectionClass($this));
    }

    
    public function getHelp()
    {
        return $this->parseDocCommentDetail(new \ReflectionClass($this));
    }

    
    public function getActionHelpSummary($action)
    {
        return $this->parseDocCommentSummary($this->getActionMethodReflection($action));
    }

    
    public function getActionHelp($action)
    {
        return $this->parseDocCommentDetail($this->getActionMethodReflection($action));
    }

    
    public function getActionArgsHelp($action)
    {
        $method = $this->getActionMethodReflection($action);
        $tags = $this->parseDocCommentTags($method);
        $params = isset($tags['param']) ? (array) $tags['param'] : [];

        $args = [];

        
        foreach ($method->getParameters() as $i => $reflection) {
            $name = $reflection->getName();
            $tag = isset($params[$i]) ? $params[$i] : '';
            if (preg_match('/^(\S+)\s+(\$\w+\s+)?(.*)/s', $tag, $matches)) {
                $type = $matches[1];
                $comment = $matches[3];
            } else {
                $type = null;
                $comment = $tag;
            }
            if ($reflection->isDefaultValueAvailable()) {
                $args[$name] = [
                    'required' => false,
                    'type' => $type,
                    'default' => $reflection->getDefaultValue(),
                    'comment' => $comment,
                ];
            } else {
                $args[$name] = [
                    'required' => true,
                    'type' => $type,
                    'default' => null,
                    'comment' => $comment,
                ];
            }
        }
        return $args;
    }

    
    public function getActionOptionsHelp($action)
    {
        $optionNames = $this->options($action->id);
        if (empty($optionNames)) {
            return [];
        }

        $class = new \ReflectionClass($this);
        $options = [];
        foreach ($class->getProperties() as $property) {
            $name = $property->getName();
            if (!in_array($name, $optionNames, true)) {
                continue;
            }
            $defaultValue = $property->getValue($this);
            $tags = $this->parseDocCommentTags($property);
            if (isset($tags['var']) || isset($tags['property'])) {
                $doc = isset($tags['var']) ? $tags['var'] : $tags['property'];
                if (is_array($doc)) {
                    $doc = reset($doc);
                }
                if (preg_match('/^(\S+)(.*)/s', $doc, $matches)) {
                    $type = $matches[1];
                    $comment = $matches[2];
                } else {
                    $type = null;
                    $comment = $doc;
                }
                $options[$name] = [
                    'type' => $type,
                    'default' => $defaultValue,
                    'comment' => $comment,
                ];
            } else {
                $options[$name] = [
                    'type' => null,
                    'default' => $defaultValue,
                    'comment' => '',
                ];
            }
        }
        return $options;
    }

    private $_reflections = [];

    
    protected function getActionMethodReflection($action)
    {
        if (!isset($this->_reflections[$action->id])) {
            if ($action instanceof InlineAction) {
                $this->_reflections[$action->id] = new \ReflectionMethod($this, $action->actionMethod);
            } else {
                $this->_reflections[$action->id] = new \ReflectionMethod($action, 'run');
            }
        }
        return $this->_reflections[$action->id];
    }

    
    protected function parseDocCommentTags($reflection)
    {
        $comment = $reflection->getDocComment();
        $comment = "@description \n" . strtr(trim(preg_replace('/^\s*\**( |\t)?/m', '', trim($comment, '/'))), "\r", '');
        $parts = preg_split('/^\s*@/m', $comment, -1, PREG_SPLIT_NO_EMPTY);
        $tags = [];
        foreach ($parts as $part) {
            if (preg_match('/^(\w+)(.*)/ms', trim($part), $matches)) {
                $name = $matches[1];
                if (!isset($tags[$name])) {
                    $tags[$name] = trim($matches[2]);
                } elseif (is_array($tags[$name])) {
                    $tags[$name][] = trim($matches[2]);
                } else {
                    $tags[$name] = [$tags[$name], trim($matches[2])];
                }
            }
        }
        return $tags;
    }

    
    protected function parseDocCommentSummary($reflection)
    {
        $docLines = preg_split('~\R~u', $reflection->getDocComment());
        if (isset($docLines[1])) {
            return trim($docLines[1], "\t *");
        }
        return '';
    }

    
    protected function parseDocCommentDetail($reflection)
    {
        $comment = strtr(trim(preg_replace('/^\s*\**( |\t)?/m', '', trim($reflection->getDocComment(), '/'))), "\r", '');
        if (preg_match('/^\s*@\w+/m', $comment, $matches, PREG_OFFSET_CAPTURE)) {
            $comment = trim(substr($comment, 0, $matches[0][1]));
        }
        if ($comment !== '') {
            return rtrim(Console::renderColoredString(Console::markdownToAnsi($comment)));
        }
        return '';
    }
}
