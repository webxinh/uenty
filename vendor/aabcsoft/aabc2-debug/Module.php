<?php


namespace aabc\debug;

use Aabc;
use aabc\base\Application;
use aabc\base\BootstrapInterface;
use aabc\helpers\Json;
use aabc\web\Response;
use aabc\helpers\Html;
use aabc\helpers\Url;
use aabc\web\View;
use aabc\web\ForbiddenHttpException;


class Module extends \aabc\base\Module implements BootstrapInterface
{
    const DEFAULT_IDE_TRACELINE = '<a href="ide://open?url=file://{file}&line={line}">{text}</a>';

    
    public $allowedIPs = ['127.0.0.1', '::1'];
    
    public $allowedHosts = [];
    
    public $controllerNamespace = 'aabc\debug\controllers';
    
    public $logTarget;
    
    public $panels = [];
    
    public $defaultPanel = 'log';
    
    public $dataPath = '@runtime/debug';
    
    public $fileMode;
    
    public $dirMode = 0775;
    
    public $historySize = 50;
    
    public $enableDebugLogs = false;
    
    public $traceLine = self::DEFAULT_IDE_TRACELINE;

    
    private static $_aabcLogo = 'data:image/png;base64,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';


    
    public static function getAabcLogo()
    {
        return self::$_aabcLogo;
    }

    
    public static function setAabcLogo($logo)
    {
        self::$_aabcLogo = $logo;
    }

    
    public function init()
    {
        parent::init();
        $this->dataPath = Aabc::getAlias($this->dataPath);
        $this->initPanels();
    }

    
    protected function initPanels()
    {
        // merge custom panels and core panels so that they are ordered mainly by custom panels
        if (empty($this->panels)) {
            $this->panels = $this->corePanels();
        } else {
            $corePanels = $this->corePanels();
            foreach ($corePanels as $id => $config) {
                if (isset($this->panels[$id])) {
                    unset($corePanels[$id]);
                }
            }
            $this->panels = array_filter(array_merge($corePanels, $this->panels));
        }

        foreach ($this->panels as $id => $config) {
            if (is_string($config)) {
                $config = ['class' => $config];
            }
            $config['module'] = $this;
            $config['id'] = $id;
            $this->panels[$id] = Aabc::createObject($config);
        }
    }

    
    public function bootstrap($app)
    {
        $this->logTarget = Aabc::$app->getLog()->targets['debug'] = new LogTarget($this);

        // delay attaching event handler to the view component after it is fully configured
        $app->on(Application::EVENT_BEFORE_REQUEST, function () use ($app) {
            $app->getView()->on(View::EVENT_END_BODY, [$this, 'renderToolbar']);
            $app->getResponse()->on(Response::EVENT_AFTER_PREPARE, [$this, 'setDebugHeaders']);
        });

        $app->getUrlManager()->addRules([
            [
                'class' => 'aabc\web\UrlRule',
                'route' => $this->id,
                'pattern' => $this->id,
            ],
            [
                'class' => 'aabc\web\UrlRule',
                'route' => $this->id . '/<controller>/<action>',
                'pattern' => $this->id . '/<controller:[\w\-]+>/<action:[\w\-]+>',
            ]
        ], false);
    }

    
    public function beforeAction($action)
    {
        if (!$this->enableDebugLogs) {
            foreach (Aabc::$app->getLog()->targets as $target) {
                $target->enabled = false;
            }
        }

        if (!parent::beforeAction($action)) {
            return false;
        }

        // do not display debug toolbar when in debug view mode
        Aabc::$app->getView()->off(View::EVENT_END_BODY, [$this, 'renderToolbar']);
        Aabc::$app->getResponse()->off(Response::EVENT_AFTER_PREPARE, [$this, 'setDebugHeaders']);

        if ($this->checkAccess()) {
            $this->resetGlobalSettings();
            return true;
        } elseif ($action->id === 'toolbar') {
            // Accessing toolbar remotely is normal. Do not throw exception.
            return false;
        } else {
            throw new ForbiddenHttpException('You are not allowed to access this page.');
        }
    }

    
    public function setDebugHeaders($event)
    {
        if (!$this->checkAccess() || !Aabc::$app->getRequest()->getIsAjax()) {
            return;
        }
        $url = Url::toRoute(['/' . $this->id . '/default/view',
            'tag' => $this->logTarget->tag,
        ]);
        $event->sender->getHeaders()
            ->set('X-Debug-Tag', $this->logTarget->tag)
            ->set('X-Debug-Duration', number_format((microtime(true) - AABC_BEGIN_TIME) * 1000 + 1))
            ->set('X-Debug-Link', $url);
    }

    
    protected function resetGlobalSettings()
    {
        Aabc::$app->assetManager->bundles = [];
    }

    
    public function getToolbarHtml()
    {
        $url = Url::toRoute(['/' . $this->id . '/default/toolbar',
            'tag' => $this->logTarget->tag,
        ]);
        return '<div id="aabc-debug-toolbar" data-url="' . Html::encode($url) . '" style="display:none" class="aabc-debug-toolbar-bottom"></div>';
    }

    
    public function renderToolbar($event)
    {
        if (!$this->checkAccess() || Aabc::$app->getRequest()->getIsAjax()) {
            return;
        }

        /* @var $view View */
        $view = $event->sender;
        echo $view->renderDynamic('return Aabc::$app->getModule("debug")->getToolbarHtml();');

        // echo is used in order to support cases where asset manager is not available
        echo '<style>' . $view->renderPhpFile(__DIR__ . '/assets/toolbar.css') . '</style>';
        echo '<script>' . $view->renderPhpFile(__DIR__ . '/assets/toolbar.js') . '</script>';
    }

    
    protected function checkAccess()
    {
        $ip = Aabc::$app->getRequest()->getUserIP();
        foreach ($this->allowedIPs as $filter) {
            if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
                return true;
            }
        }
        foreach ($this->allowedHosts as $hostname) {
            $filter = gethostbyname($hostname);
            if ($filter === $ip) {
                return true;
            }
        }
        Aabc::warning('Access to debugger is denied due to IP address restriction. The requesting IP address is ' . $ip, __METHOD__);
        return false;
    }

    
    protected function corePanels()
    {
        return [
            'config' => ['class' => 'aabc\debug\panels\ConfigPanel'],
            'request' => ['class' => 'aabc\debug\panels\RequestPanel'],
            'log' => ['class' => 'aabc\debug\panels\LogPanel'],
            'profiling' => ['class' => 'aabc\debug\panels\ProfilingPanel'],
            'db' => ['class' => 'aabc\debug\panels\DbPanel'],
            'assets' => ['class' => 'aabc\debug\panels\AssetPanel'],
            'mail' => ['class' => 'aabc\debug\panels\MailPanel'],
            'timeline' => ['class' => 'aabc\debug\panels\TimelinePanel']
        ];
    }

    
    protected function defaultVersion()
    {
        $packageInfo = Json::decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'composer.json'));
        $extensionName = $packageInfo['name'];
        if (isset(Aabc::$app->extensions[$extensionName])) {
            return Aabc::$app->extensions[$extensionName]['version'];
        }
        return parent::defaultVersion();
    }
}
