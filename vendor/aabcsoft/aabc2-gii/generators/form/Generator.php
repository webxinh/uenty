<?php


namespace aabc\gii\generators\form;

use Aabc;
use aabc\base\Model;
use aabc\gii\CodeFile;


class Generator extends \aabc\gii\Generator
{
    public $modelClass;
    public $viewPath = '@app/views';
    public $viewName;
    public $scenarioName;


    
    public function getName()
    {
        return 'Form Generator';
    }

    
    public function getDescription()
    {
        return 'This generator generates a view script file that displays a form to collect input for the specified model class.';
    }

    
    public function generate()
    {
        $files = [];
        $files[] = new CodeFile(
            Aabc::getAlias($this->viewPath) . '/' . $this->viewName . '.php',
            $this->render('form.php')
        );

        return $files;
    }

    
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['modelClass', 'viewName', 'scenarioName', 'viewPath'], 'filter', 'filter' => 'trim'],
            [['modelClass', 'viewName', 'viewPath'], 'required'],
            [['modelClass'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['modelClass'], 'validateClass', 'params' => ['extends' => Model::className()]],
            [['viewName'], 'match', 'pattern' => '/^\w+[\\-\\/\w]*$/', 'message' => 'Only word characters, dashes and slashes are allowed.'],
            [['viewPath'], 'match', 'pattern' => '/^@?\w+[\\-\\/\w]*$/', 'message' => 'Only word characters, dashes, slashes and @ are allowed.'],
            [['viewPath'], 'validateViewPath'],
            [['scenarioName'], 'match', 'pattern' => '/^[\w\\-]+$/', 'message' => 'Only word characters and dashes are allowed.'],
            [['enableI18N'], 'boolean'],
            [['messageCategory'], 'validateMessageCategory', 'skipOnEmpty' => false],
        ]);
    }

    
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'modelClass' => 'Model Class',
            'viewName' => 'View Name',
            'viewPath' => 'View Path',
            'scenarioName' => 'Scenario',
        ]);
    }

    
    public function requiredTemplates()
    {
        return ['form.php', 'action.php'];
    }

    
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(), ['viewPath', 'scenarioName']);
    }

    
    public function hints()
    {
        return array_merge(parent::hints(), [
            'modelClass' => 'This is the model class for collecting the form input. You should provide a fully qualified class name, e.g., <code>app\models\Post</code>.',
            'viewName' => 'This is the view name with respect to the view path. For example, <code>site/index</code> would generate a <code>site/index.php</code> view file under the view path.',
            'viewPath' => 'This is the root view path to keep the generated view files. You may provide either a directory or a path alias, e.g., <code>@app/views</code>.',
            'scenarioName' => 'This is the scenario to be used by the model when collecting the form input. If empty, the default scenario will be used.',
        ]);
    }

    
    public function successMessage()
    {
        $code = highlight_string($this->render('action.php'), true);

        return <<<EOD
<p>The form has been generated successfully.</p>
<p>You may add the following code in an appropriate controller class to invoke the view:</p>
<pre>$code</pre>
EOD;
    }

    
    public function validateViewPath()
    {
        $path = Aabc::getAlias($this->viewPath, false);
        if ($path === false || !is_dir($path)) {
            $this->addError('viewPath', 'View path does not exist.');
        }
    }

    
    public function getModelAttributes()
    {
        /* @var $model Model */
        $model = new $this->modelClass();
        if (!empty($this->scenarioName)) {
            $model->setScenario($this->scenarioName);
        }

        return $model->safeAttributes();
    }
}
