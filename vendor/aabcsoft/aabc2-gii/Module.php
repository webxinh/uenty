<?php


namespace aabc\gii;

use Aabc;
use aabc\base\BootstrapInterface;
use aabc\web\ForbiddenHttpException;


class Module extends \aabc\base\Module implements BootstrapInterface
{
    
    public $controllerNamespace = 'aabc\gii\controllers';
    
    public $allowedIPs = ['127.0.0.1', '::1'];
    
    public $generators = [];
    
    public $newFileMode = 0666;
    
    public $newDirMode = 0777;


    
    public function bootstrap($app)
    {
        if ($app instanceof \aabc\web\Application) {
            $app->getUrlManager()->addRules([
                ['class' => 'aabc\web\UrlRule', 'pattern' => $this->id, 'route' => $this->id . '/default/index'],
                ['class' => 'aabc\web\UrlRule', 'pattern' => $this->id . '/<id:\w+>', 'route' => $this->id . '/default/view'],
                ['class' => 'aabc\web\UrlRule', 'pattern' => $this->id . '/<controller:[\w\-]+>/<action:[\w\-]+>', 'route' => $this->id . '/<controller>/<action>'],
            ], false);
        } elseif ($app instanceof \aabc\console\Application) {
            $app->controllerMap[$this->id] = [
                'class' => 'aabc\gii\console\GenerateController',
                'generators' => array_merge($this->coreGenerators(), $this->generators),
                'module' => $this,
            ];
        }
    }

    
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (Aabc::$app instanceof \aabc\web\Application && !$this->checkAccess()) {
            throw new ForbiddenHttpException('You are not allowed to access this page.');
        }

        foreach (array_merge($this->coreGenerators(), $this->generators) as $id => $config) {
            if (is_object($config)) {
                $this->generators[$id] = $config;
            } else {
                $this->generators[$id] = Aabc::createObject($config);
            }
        }

        $this->resetGlobalSettings();

        return true;
    }

    
    protected function resetGlobalSettings()
    {
        if (Aabc::$app instanceof \aabc\web\Application) {
            Aabc::$app->assetManager->bundles = [];
        }
    }

    
    protected function checkAccess()
    {
        $ip = Aabc::$app->getRequest()->getUserIP();
        foreach ($this->allowedIPs as $filter) {
            if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
                return true;
            }
        }
        Aabc::warning('Access to Gii is denied due to IP address restriction. The requested IP is ' . $ip, __METHOD__);

        return false;
    }

    
    protected function coreGenerators()
    {
        return [
            'model' => ['class' => 'aabc\gii\generators\model\Generator'],
            'crud' => ['class' => 'aabc\gii\generators\crud\Generator'],
            'controller' => ['class' => 'aabc\gii\generators\controller\Generator'],
            'form' => ['class' => 'aabc\gii\generators\form\Generator'],
            'module' => ['class' => 'aabc\gii\generators\module\Generator'],
            'extension' => ['class' => 'aabc\gii\generators\extension\Generator'],
        ];
    }
}
