<?php


namespace aabc\gii;

use Aabc;
use ReflectionClass;
use aabc\base\InvalidConfigException;
use aabc\base\Model;
use aabc\helpers\VarDumper;
use aabc\web\View;


abstract class Generator extends Model
{
    
    public $templates = [];
    
    public $template = 'default';
    
    public $enableI18N = false;
    
    public $messageCategory = 'app';


    
    abstract public function getName();
    
    abstract public function generate();

    
    public function init()
    {
        parent::init();
        if (!isset($this->templates['default'])) {
            $this->templates['default'] = $this->defaultTemplate();
        }
        foreach ($this->templates as $i => $template) {
            $this->templates[$i] = Aabc::getAlias($template);
        }
    }

    
    public function attributeLabels()
    {
        return [
            'enableI18N' => 'Enable I18N',
            'messageCategory' => 'Message Category',
        ];
    }

    
    public function requiredTemplates()
    {
        return [];
    }

    
    public function stickyAttributes()
    {
        return ['template', 'enableI18N', 'messageCategory'];
    }

    
    public function hints()
    {
        return [
            'enableI18N' => 'This indicates whether the generator should generate strings using <code>Aabc::t()</code> method.
                Set this to <code>true</code> if you are planning to make your application translatable.',
            'messageCategory' => 'This is the category used by <code>Aabc::t()</code> in case you enable I18N.',
        ];
    }

    
    public function autoCompleteData()
    {
        return [];
    }

    
    public function successMessage()
    {
        return 'The code has been generated successfully.';
    }

    
    public function formView()
    {
        $class = new ReflectionClass($this);

        return dirname($class->getFileName()) . '/form.php';
    }

    
    public function defaultTemplate()
    {
        $class = new ReflectionClass($this);

        return dirname($class->getFileName()) . '/default';
    }

    
    public function getDescription()
    {
        return '';
    }

    
    public function rules()
    {
        return [
            [['template'], 'required', 'message' => 'A code template must be selected.'],
            [['template'], 'validateTemplate'],
        ];
    }

    
    public function loadStickyAttributes()
    {
        $stickyAttributes = $this->stickyAttributes();
        $path = $this->getStickyDataFile();
        if (is_file($path)) {
            $result = json_decode(file_get_contents($path), true);
            if (is_array($result)) {
                foreach ($stickyAttributes as $name) {
                    if (isset($result[$name])) {
                        $this->$name = $result[$name];
                    }
                }
            }
        }
    }

    
    public function saveStickyAttributes()
    {
        $stickyAttributes = $this->stickyAttributes();
        $stickyAttributes[] = 'template';
        $values = [];
        foreach ($stickyAttributes as $name) {
            $values[$name] = $this->$name;
        }
        $path = $this->getStickyDataFile();
        @mkdir(dirname($path), 0755, true);
        file_put_contents($path, json_encode($values));
    }

    
    public function getStickyDataFile()
    {
        return Aabc::$app->getRuntimePath() . '/gii-' . Aabc::getVersion() . '/' . str_replace('\\', '-', get_class($this)) . '.json';
    }

    
    public function save($files, $answers, &$results)
    {
        $lines = ['Generating code using template "' . $this->getTemplatePath() . '"...'];
        $hasError = false;
        foreach ($files as $file) {
            $relativePath = $file->getRelativePath();
            if (isset($answers[$file->id]) && !empty($answers[$file->id]) && $file->operation !== CodeFile::OP_SKIP) {
                $error = $file->save();
                if (is_string($error)) {
                    $hasError = true;
                    $lines[] = "generating $relativePath\n<span class=\"error\">$error</span>";
                } else {
                    $lines[] = $file->operation === CodeFile::OP_CREATE ? " generated $relativePath" : " overwrote $relativePath";
                }
            } else {
                $lines[] = "   skipped $relativePath";
            }
        }
        $lines[] = "done!\n";
        $results = implode("\n", $lines);

        return !$hasError;
    }

    
    public function getTemplatePath()
    {
        if (isset($this->templates[$this->template])) {
            return $this->templates[$this->template];
        } else {
            throw new InvalidConfigException("Unknown template: {$this->template}");
        }
    }

    
    public function render($template, $params = [])
    {
        $view = new View();
        $params['generator'] = $this;

        return $view->renderFile($this->getTemplatePath() . '/' . $template, $params, $this);
    }

    
    public function validateTemplate()
    {
        $templates = $this->templates;
        if (!isset($templates[$this->template])) {
            $this->addError('template', 'Invalid template selection.');
        } else {
            $templatePath = $this->templates[$this->template];
            foreach ($this->requiredTemplates() as $template) {
                if (!is_file($templatePath . '/' . $template)) {
                    $this->addError('template', "Unable to find the required code template file '$template'.");
                }
            }
        }
    }

    
    public function validateClass($attribute, $params)
    {
        $class = $this->$attribute;
        try {
            if (class_exists($class)) {
                if (isset($params['extends'])) {
                    if (ltrim($class, '\\') !== ltrim($params['extends'], '\\') && !is_subclass_of($class, $params['extends'])) {
                        $this->addError($attribute, "'$class' must extend from {$params['extends']} or its child class.");
                    }
                }
            } else {
                $this->addError($attribute, "Class '$class' does not exist or has syntax error.");
            }
        } catch (\Exception $e) {
            $this->addError($attribute, "Class '$class' does not exist or has syntax error.");
        }
    }

    
    public function validateNewClass($attribute, $params)
    {
        $class = ltrim($this->$attribute, '\\');
        if (($pos = strrpos($class, '\\')) === false) {
            $this->addError($attribute, "The class name must contain fully qualified namespace name.");
        } else {
            $ns = substr($class, 0, $pos);
            $path = Aabc::getAlias('@' . str_replace('\\', '/', $ns), false);
            if ($path === false) {
                $this->addError($attribute, "The class namespace is invalid: $ns");
            } elseif (!is_dir($path)) {
                $this->addError($attribute, "Please make sure the directory containing this class exists: $path");
            }
        }
    }

    
    public function validateMessageCategory()
    {
        if ($this->enableI18N && empty($this->messageCategory)) {
            $this->addError('messageCategory', "Message Category cannot be blank.");
        }
    }

    
    public function isReservedKeyword($value)
    {
        static $keywords = [
            '__class__',
            '__dir__',
            '__file__',
            '__function__',
            '__line__',
            '__method__',
            '__namespace__',
            '__trait__',
            'abstract',
            'and',
            'array',
            'as',
            'break',
            'case',
            'catch',
            'callable',
            'cfunction',
            'class',
            'clone',
            'const',
            'continue',
            'declare',
            'default',
            'die',
            'do',
            'echo',
            'else',
            'elseif',
            'empty',
            'enddeclare',
            'endfor',
            'endforeach',
            'endif',
            'endswitch',
            'endwhile',
            'eval',
            'exception',
            'exit',
            'extends',
            'final',
            'finally',
            'for',
            'foreach',
            'function',
            'global',
            'goto',
            'if',
            'implements',
            'include',
            'include_once',
            'instanceof',
            'insteadof',
            'interface',
            'isset',
            'list',
            'namespace',
            'new',
            'old_function',
            'or',
            'parent',
            'php_user_filter',
            'print',
            'private',
            'protected',
            'public',
            'require',
            'require_once',
            'return',
            'static',
            'switch',
            'this',
            'throw',
            'trait',
            'try',
            'unset',
            'use',
            'var',
            'while',
            'xor',
        ];

        return in_array(strtolower($value), $keywords, true);
    }

    
    public function generateString($string = '', $placeholders = [])
    {
        $string = addslashes($string);
        if ($this->enableI18N) {
            // If there are placeholders, use them
            if (!empty($placeholders)) {
                $ph = ', ' . VarDumper::export($placeholders);
            } else {
                $ph = '';
            }
            $str = "Aabc::t('" . $this->messageCategory . "', '" . $string . "'" . $ph . ")";
        } else {
            // No I18N, replace placeholders by real words, if any
            if (!empty($placeholders)) {
                $phKeys = array_map(function($word) {
                    return '{' . $word . '}';
                }, array_keys($placeholders));
                $phValues = array_values($placeholders);
                $str = "'" . str_replace($phKeys, $phValues, $string) . "'";
            } else {
                // No placeholders, just the given string
                $str = "'" . $string . "'";
            }
        }
        return $str;
    }
}
