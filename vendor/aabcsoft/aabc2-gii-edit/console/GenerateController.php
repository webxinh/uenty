<?php


namespace aabc\gii\console;

use Aabc;
use aabc\base\InlineAction;
use aabc\console\Controller;


class GenerateController extends Controller
{
    
    public $module;
    
    public $overwrite = false;
    
    public $generators = [];

    
    private $_options = [];


    
    public function __get($name)
    {
        return isset($this->_options[$name]) ? $this->_options[$name] : null;
    }

    
    public function __set($name, $value)
    {
        $this->_options[$name] = $value;
    }

    
    public function init()
    {
        parent::init();
        foreach ($this->generators as $id => $config) {
            $this->generators[$id] = Aabc::createObject($config);
        }
    }

    
    public function createAction($id)
    {
        
        $action = parent::createAction($id);
        foreach ($this->_options as $name => $value) {
            $action->generator->$name = $value;
        }
        return $action;
    }

    
    public function actions()
    {
        $actions = [];
        foreach ($this->generators as $name => $generator) {
            $actions[$name] = [
                'class' => 'aabc\gii\console\GenerateAction',
                'generator' => $generator,
            ];
        }
        return $actions;
    }

    public function actionIndex()
    {
        $this->run('/help', ['gii']);
    }

    
    public function getUniqueID()
    {
        return $this->id;
    }

    
    public function options($id)
    {
        $options = parent::options($id);
        $options[] = 'overwrite';

        if (!isset($this->generators[$id])) {
            return $options;
        }

        $attributes = $this->generators[$id]->attributes;
        unset($attributes['templates']);
        return array_merge(
            $options,
            array_keys($attributes)
        );
    }

    
    public function getActionHelpSummary($action)
    {
        if ($action instanceof InlineAction) {
            return parent::getActionHelpSummary($action);
        } else {
            
            return $action->generator->getName();
        }
    }

    
    public function getActionHelp($action)
    {
        if ($action instanceof InlineAction) {
            return parent::getActionHelp($action);
        } else {
            
            $description = $action->generator->getDescription();
            return wordwrap(preg_replace('/\s+/', ' ', $description));
        }
    }

    
    public function getActionArgsHelp($action)
    {
        return [];
    }

    
    public function getActionOptionsHelp($action)
    {
        if ($action instanceof InlineAction) {
            return parent::getActionOptionsHelp($action);
        }
        
        $attributes = $action->generator->attributes;
        unset($attributes['templates']);
        $hints = $action->generator->hints();

        $options = parent::getActionOptionsHelp($action);
        foreach ($attributes as $name => $value) {
            $type = gettype($value);
            $options[$name] = [
                'type' => $type === 'NULL' ? 'string' : $type,
                'required' => $value === null && $action->generator->isAttributeRequired($name),
                'default' => $value,
                'comment' => isset($hints[$name]) ? $this->formatHint($hints[$name]) : '',
            ];
        }

        return $options;
    }

    protected function formatHint($hint)
    {
        $hint = preg_replace('%<code>(.*?)</code>%', '\1', $hint);
        $hint = preg_replace('/\s+/', ' ', $hint);
        return wordwrap($hint);
    }
}
