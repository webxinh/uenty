<?php


namespace aabc\gii\generators\controller;

use Aabc;
use aabc\gii\CodeFile;
use aabc\helpers\Html;
use aabc\helpers\Inflector;
use aabc\helpers\StringHelper;


class Generator extends \aabc\gii\Generator
{
    
    public $controllerClass;
    
    public $viewPath;
    
    public $baseClass = 'aabc\web\Controller';
    
    public $actions = 'index';


    
    public function getName()
    {
        return 'Controller Generator';
    }

    
    public function getDescription()
    {
        return 'This generator helps you to quickly generate a new controller class with
            one or several controller actions and their corresponding views.';
    }

    
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['controllerClass', 'actions', 'baseClass'], 'filter', 'filter' => 'trim'],
            [['controllerClass', 'baseClass'], 'required'],
            ['controllerClass', 'match', 'pattern' => '/^[\w\\\\]*Controller$/', 'message' => 'Only word characters and backslashes are allowed, and the class name must end with "Controller".'],
            ['controllerClass', 'validateNewClass'],
            ['baseClass', 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
            ['actions', 'match', 'pattern' => '/^[a-z][a-z0-9\\-,\\s]*$/', 'message' => 'Only a-z, 0-9, dashes (-), spaces and commas are allowed.'],
            ['viewPath', 'safe'],
        ]);
    }

    
    public function attributeLabels()
    {
        return [
            'baseClass' => 'Base Class',
            'controllerClass' => 'Controller Class',
            'viewPath' => 'View Path',
            'actions' => 'Action IDs',
        ];
    }

    
    public function requiredTemplates()
    {
        return [
            'controller.php',
            'view.php',
        ];
    }

    
    public function stickyAttributes()
    {
        return ['baseClass'];
    }

    
    public function hints()
    {
        return [
            'controllerClass' => 'This is the name of the controller class to be generated. You should
                provide a fully qualified namespaced class (e.g. <code>app\controllers\PostController</code>),
                and class name should be in CamelCase ending with the word <code>Controller</code>. Make sure the class
                is using the same namespace as specified by your application\'s controllerNamespace property.',
            'actions' => 'Provide one or multiple action IDs to generate empty action method(s) in the controller. Separate multiple action IDs with commas or spaces.
                Action IDs should be in lower case. For example:
                <ul>
                    <li><code>index</code> generates <code>actionIndex()</code></li>
                    <li><code>create-order</code> generates <code>actionCreateOrder()</code></li>
                </ul>',
            'viewPath' => 'Specify the directory for storing the view scripts for the controller. You may use path alias here, e.g.,
                <code>/var/www/basic/controllers/views/order</code>, <code>@app/views/order</code>. If not set, it will default
                to <code>@app/views/ControllerID</code>',
            'baseClass' => 'This is the class that the new controller class will extend from. Please make sure the class exists and can be autoloaded.',
        ];
    }

    
    public function successMessage()
    {
        $actions = $this->getActionIDs();
        if (in_array('index', $actions)) {
            $route = $this->getControllerID() . '/index';
        } else {
            $route = $this->getControllerID() . '/' . reset($actions);
        }
        $link = Html::a('try it now', Aabc::$app->getUrlManager()->createUrl($route), ['target' => '_blank']);

        return "The controller has been generated successfully. You may $link.";
    }

    
    public function generate()
    {
        $files = [];

        $files[] = new CodeFile(
            $this->getControllerFile(),
            $this->render('controller.php')
        );

        foreach ($this->getActionIDs() as $action) {
            $files[] = new CodeFile(
                $this->getViewFile($action),
                $this->render('view.php', ['action' => $action])
            );
        }

        return $files;
    }

    
    public function getActionIDs()
    {
        $actions = array_unique(preg_split('/[\s,]+/', $this->actions, -1, PREG_SPLIT_NO_EMPTY));
        sort($actions);

        return $actions;
    }

    
    public function getControllerFile()
    {
        return Aabc::getAlias('@' . str_replace('\\', '/', $this->controllerClass)) . '.php';
    }

    
    public function getControllerID()
    {
        $name = StringHelper::basename($this->controllerClass);
        return Inflector::camel2id(substr($name, 0, strlen($name) - 10));
    }

    
    public function getViewFile($action)
    {
        if (empty($this->viewPath)) {
            return Aabc::getAlias('@app/views/' . $this->getControllerID() . "/$action.php");
        } else {
            return Aabc::getAlias($this->viewPath . "/$action.php");
        }
    }

    
    public function getControllerNamespace()
    {
        $name = StringHelper::basename($this->controllerClass);
        return ltrim(substr($this->controllerClass, 0, - (strlen($name) + 1)), '\\');
    }
}
