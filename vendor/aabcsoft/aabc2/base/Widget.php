<?php


namespace aabc\base;

use Aabc;
use ReflectionClass;


class Widget extends Component implements ViewContextInterface
{
    
    const EVENT_INIT = 'init';
    
    const EVENT_BEFORE_RUN = 'beforeRun';
    
    const EVENT_AFTER_RUN = 'afterRun';

    
    public static $counter = 0;
    
    public static $autoIdPrefix = 'w';
    
    public static $stack = [];


    
    public function init()
    {
        parent::init();
        $this->trigger(self::EVENT_INIT);
    }

    
    public static function begin($config = [])
    {
        $config['class'] = get_called_class();
        /* @var $widget Widget */
        $widget = Aabc::createObject($config);
        static::$stack[] = $widget;

        return $widget;
    }

    
    public static function end()
    {
        if (!empty(static::$stack)) {
            $widget = array_pop(static::$stack);
            if (get_class($widget) === get_called_class()) {
                /* @var $widget Widget */
                if ($widget->beforeRun()) {
                    $result = $widget->run();
                    $result = $widget->afterRun($result);
                    echo $result;
                }
                return $widget;
            } else {
                throw new InvalidCallException('Expecting end() of ' . get_class($widget) . ', found ' . get_called_class());
            }
        } else {
            throw new InvalidCallException('Unexpected ' . get_called_class() . '::end() call. A matching begin() is not found.');
        }
    }

    
    public static function widget($config = [])
    {
        ob_start();
        ob_implicit_flush(false);
        try {
            /* @var $widget Widget */
            $config['class'] = get_called_class();
            $widget = Aabc::createObject($config);
            $out = '';
            if ($widget->beforeRun()) {
                $result = $widget->run();
                $out = $widget->afterRun($result);
            }
        } catch (\Exception $e) {
            // close the output buffer opened above if it has not been closed already
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            throw $e;
        }

        return ob_get_clean() . $out;
    }

    private $_id;

    
    public function getId($autoGenerate = true)
    {
        if ($autoGenerate && $this->_id === null) {
            $this->_id = static::$autoIdPrefix . static::$counter++;
        }

        return $this->_id;
    }

    
    public function setId($value)
    {
        $this->_id = $value;
    }

    private $_view;

    
    public function getView()
    {
        if ($this->_view === null) {
            $this->_view = Aabc::$app->getView();
        }

        return $this->_view;
    }

    
    public function setView($view)
    {
        $this->_view = $view;
    }

    
    public function run()
    {
    }

    
    public function render($view, $params = [])
    {
        return $this->getView()->render($view, $params, $this);
    }

    
    public function renderFile($file, $params = [])
    {
        return $this->getView()->renderFile($file, $params, $this);
    }

    
    public function getViewPath()
    {
        $class = new ReflectionClass($this);

        return dirname($class->getFileName()) . DIRECTORY_SEPARATOR . 'views';
    }
    
    
    public function beforeRun()
    {
        $event = new WidgetEvent();
        $this->trigger(self::EVENT_BEFORE_RUN, $event);
        return $event->isValid;
    }

    
    public function afterRun($result)
    {
        $event = new WidgetEvent();
        $event->result = $result;
        $this->trigger(self::EVENT_AFTER_RUN, $event);
        return $event->result;
    }
}
