<?php


namespace aabc\base;

use Aabc;
use aabc\helpers\FileHelper;
use aabc\widgets\Block;
use aabc\widgets\ContentDecorator;
use aabc\widgets\FragmentCache;


class View extends Component
{
    
    const EVENT_BEGIN_PAGE = 'beginPage';
    
    const EVENT_END_PAGE = 'endPage';
    
    const EVENT_BEFORE_RENDER = 'beforeRender';
    
    const EVENT_AFTER_RENDER = 'afterRender';

    
    public $context;
    
    public $params = [];
    
    public $renderers;
    
    public $defaultExtension = 'php';
    
    public $theme;
    
    public $blocks;
    
    public $cacheStack = [];
    
    public $dynamicPlaceholders = [];

    
    private $_viewFiles = [];


    
    public function init()
    {
        parent::init();
        if (is_array($this->theme)) {
            if (!isset($this->theme['class'])) {
                $this->theme['class'] = 'aabc\base\Theme';
            }
            $this->theme = Aabc::createObject($this->theme);
        } elseif (is_string($this->theme)) {
            $this->theme = Aabc::createObject($this->theme);
        }
    }

    
    public function render($view, $params = [], $context = null)
    {
        $viewFile = $this->findViewFile($view, $context);
        return $this->renderFile($viewFile, $params, $context);
    }

    
    protected function findViewFile($view, $context = null)
    {
        if (strncmp($view, '@', 1) === 0) {
            // e.g. "@app/views/main"
            $file = Aabc::getAlias($view);
        } elseif (strncmp($view, '//', 2) === 0) {
            // e.g. "//layouts/main"
            $file = Aabc::$app->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
        } elseif (strncmp($view, '/', 1) === 0) {
            // e.g. "/site/index"
            if (Aabc::$app->controller !== null) {
                $file = Aabc::$app->controller->module->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
            } else {
                throw new InvalidCallException("Unable to locate view file for view '$view': no active controller.");
            }
        } elseif ($context instanceof ViewContextInterface) {
            $file = $context->getViewPath() . DIRECTORY_SEPARATOR . $view;
        } elseif (($currentViewFile = $this->getViewFile()) !== false) {
            $file = dirname($currentViewFile) . DIRECTORY_SEPARATOR . $view;
        } else {
            throw new InvalidCallException("Unable to resolve view file for view '$view': no active view context.");
        }

        if (pathinfo($file, PATHINFO_EXTENSION) !== '') {
            return $file;
        }
        $path = $file . '.' . $this->defaultExtension;
        if ($this->defaultExtension !== 'php' && !is_file($path)) {
            $path = $file . '.php';
        }

        return $path;
    }

    
    public function renderFile($viewFile, $params = [], $context = null)
    {
        $viewFile = Aabc::getAlias($viewFile);

        if ($this->theme !== null) {
            $viewFile = $this->theme->applyTo($viewFile);
        }
        if (is_file($viewFile)) {
            $viewFile = FileHelper::localize($viewFile);
        } else {
            throw new ViewNotFoundException("The view file does not exist: $viewFile");
        }

        $oldContext = $this->context;
        if ($context !== null) {
            $this->context = $context;
        }
        $output = '';
        $this->_viewFiles[] = $viewFile;

        if ($this->beforeRender($viewFile, $params)) {
            Aabc::trace("Rendering view file: $viewFile", __METHOD__);
            $ext = pathinfo($viewFile, PATHINFO_EXTENSION);
            if (isset($this->renderers[$ext])) {
                if (is_array($this->renderers[$ext]) || is_string($this->renderers[$ext])) {
                    $this->renderers[$ext] = Aabc::createObject($this->renderers[$ext]);
                }
                /* @var $renderer ViewRenderer */
                $renderer = $this->renderers[$ext];
                $output = $renderer->render($this, $viewFile, $params);
            } else {
                $output = $this->renderPhpFile($viewFile, $params);
            }
            $this->afterRender($viewFile, $params, $output);
        }

        array_pop($this->_viewFiles);
        $this->context = $oldContext;

        return $output;
    }

    
    public function getViewFile()
    {
        return end($this->_viewFiles);
    }

    
    public function beforeRender($viewFile, $params)
    {
        $event = new ViewEvent([
            'viewFile' => $viewFile,
            'params' => $params,
        ]);
        $this->trigger(self::EVENT_BEFORE_RENDER, $event);

        return $event->isValid;
    }

    
    public function afterRender($viewFile, $params, &$output)
    {
        if ($this->hasEventHandlers(self::EVENT_AFTER_RENDER)) {
            $event = new ViewEvent([
                'viewFile' => $viewFile,
                'params' => $params,
                'output' => $output,
            ]);
            $this->trigger(self::EVENT_AFTER_RENDER, $event);
            $output = $event->output;
        }
    }

    
    public function renderPhpFile($_file_, $_params_ = [])
    {
        ob_start();
        ob_implicit_flush(false);
        extract($_params_, EXTR_OVERWRITE);
        require($_file_);

        return ob_get_clean();
    }

    
    public function renderDynamic($statements)
    {
        if (!empty($this->cacheStack)) {
            $n = count($this->dynamicPlaceholders);
            $placeholder = "<![CDATA[AABC-DYNAMIC-$n]]>";
            $this->addDynamicPlaceholder($placeholder, $statements);

            return $placeholder;
        }
        return $this->evaluateDynamicContent($statements);
    }

    
    public function addDynamicPlaceholder($placeholder, $statements)
    {
        foreach ($this->cacheStack as $cache) {
            $cache->dynamicPlaceholders[$placeholder] = $statements;
        }
        $this->dynamicPlaceholders[$placeholder] = $statements;
    }

    
    public function evaluateDynamicContent($statements)
    {
        return eval($statements);
    }

    
    public function beginBlock($id, $renderInPlace = false)
    {
        return Block::begin([
            'id' => $id,
            'renderInPlace' => $renderInPlace,
            'view' => $this,
        ]);
    }

    
    public function endBlock()
    {
        Block::end();
    }

    
    public function beginContent($viewFile, $params = [])
    {
        return ContentDecorator::begin([
            'viewFile' => $viewFile,
            'params' => $params,
            'view' => $this,
        ]);
    }

    
    public function endContent()
    {
        ContentDecorator::end();
    }

    
    public function beginCache($id, $properties = [])
    {
        $properties['id'] = $id;
        $properties['view'] = $this;
        /* @var $cache FragmentCache */
        $cache = FragmentCache::begin($properties);
        if ($cache->getCachedContent() !== false) {
            $this->endCache();

            return false;
        }
        return true;
    }

    
    public function endCache()
    {
        FragmentCache::end();
    }

    
    public function beginPage()
    {
        ob_start();
        ob_implicit_flush(false);

        $this->trigger(self::EVENT_BEGIN_PAGE);
    }

    
    public function endPage()
    {
        $this->trigger(self::EVENT_END_PAGE);
        ob_end_flush();
    }
}
