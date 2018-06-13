<?php


namespace aabc\mail;

use Aabc;
use aabc\base\Component;
use aabc\base\InvalidConfigException;
use aabc\base\ViewContextInterface;
use aabc\web\View;


abstract class BaseMailer extends Component implements MailerInterface, ViewContextInterface
{
    
    const EVENT_BEFORE_SEND = 'beforeSend';
    
    const EVENT_AFTER_SEND = 'afterSend';

    
    public $htmlLayout = 'layouts/html';
    
    public $textLayout = 'layouts/text';
    
    public $messageConfig = [];
    
    public $messageClass = 'aabc\mail\BaseMessage';
    
    public $useFileTransport = false;
    
    public $fileTransportPath = '@runtime/mail';
    
    public $fileTransportCallback;

    
    private $_view = [];
    
    private $_viewPath;


    
    public function setView($view)
    {
        if (!is_array($view) && !is_object($view)) {
            throw new InvalidConfigException('"' . get_class($this) . '::view" should be either object or configuration array, "' . gettype($view) . '" given.');
        }
        $this->_view = $view;
    }

    
    public function getView()
    {
        if (!is_object($this->_view)) {
            $this->_view = $this->createView($this->_view);
        }

        return $this->_view;
    }

    
    protected function createView(array $config)
    {
        if (!array_key_exists('class', $config)) {
            $config['class'] = View::className();
        }

        return Aabc::createObject($config);
    }

    private $_message;

    
    public function compose($view = null, array $params = [])
    {
        $message = $this->createMessage();
        if ($view === null) {
            return $message;
        }

        if (!array_key_exists('message', $params)) {
            $params['message'] = $message;
        }

        $this->_message = $message;

        if (is_array($view)) {
            if (isset($view['html'])) {
                $html = $this->render($view['html'], $params, $this->htmlLayout);
            }
            if (isset($view['text'])) {
                $text = $this->render($view['text'], $params, $this->textLayout);
            }
        } else {
            $html = $this->render($view, $params, $this->htmlLayout);
        }


        $this->_message = null;

        if (isset($html)) {
            $message->setHtmlBody($html);
        }
        if (isset($text)) {
            $message->setTextBody($text);
        } elseif (isset($html)) {
            if (preg_match('~<body[^>]*>(.*?)</body>~is', $html, $match)) {
                $html = $match[1];
            }
            // remove style and script
            $html = preg_replace('~<((style|script))[^>]*>(.*?)</\1>~is', '', $html);
            // strip all HTML tags and decoded HTML entities
            $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, Aabc::$app ? Aabc::$app->charset : 'UTF-8');
            // improve whitespace
            $text = preg_replace("~^[ \t]+~m", '', trim($text));
            $text = preg_replace('~\R\R+~mu', "\n\n", $text);
            $message->setTextBody($text);
        }
        return $message;
    }

    
    protected function createMessage()
    {
        $config = $this->messageConfig;
        if (!array_key_exists('class', $config)) {
            $config['class'] = $this->messageClass;
        }
        $config['mailer'] = $this;
        return Aabc::createObject($config);
    }

    
    public function send($message)
    {
        if (!$this->beforeSend($message)) {
            return false;
        }

        $address = $message->getTo();
        if (is_array($address)) {
            $address = implode(', ', array_keys($address));
        }
        Aabc::info('Sending email "' . $message->getSubject() . '" to "' . $address . '"', __METHOD__);

        if ($this->useFileTransport) {
            $isSuccessful = $this->saveMessage($message);
        } else {
            $isSuccessful = $this->sendMessage($message);
        }
        $this->afterSend($message, $isSuccessful);

        return $isSuccessful;
    }

    
    public function sendMultiple(array $messages)
    {
        $successCount = 0;
        foreach ($messages as $message) {
            if ($this->send($message)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    
    public function render($view, $params = [], $layout = false)
    {
        $output = $this->getView()->render($view, $params, $this);
        if ($layout !== false) {
            return $this->getView()->render($layout, ['content' => $output, 'message' => $this->_message], $this);
        } else {
            return $output;
        }
    }

    
    abstract protected function sendMessage($message);

    
    protected function saveMessage($message)
    {
        $path = Aabc::getAlias($this->fileTransportPath);
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        if ($this->fileTransportCallback !== null) {
            $file = $path . '/' . call_user_func($this->fileTransportCallback, $this, $message);
        } else {
            $file = $path . '/' . $this->generateMessageFileName();
        }
        file_put_contents($file, $message->toString());

        return true;
    }

    
    public function generateMessageFileName()
    {
        $time = microtime(true);

        return date('Ymd-His-', $time) . sprintf('%04d', (int) (($time - (int) $time) * 10000)) . '-' . sprintf('%04d', mt_rand(0, 10000)) . '.eml';
    }

    
    public function getViewPath()
    {
        if ($this->_viewPath === null) {
            $this->setViewPath('@app/mail');
        }
        return $this->_viewPath;
    }

    
    public function setViewPath($path)
    {
        $this->_viewPath = Aabc::getAlias($path);
    }

    
    public function beforeSend($message)
    {
        $event = new MailEvent(['message' => $message]);
        $this->trigger(self::EVENT_BEFORE_SEND, $event);

        return $event->isValid;
    }

    
    public function afterSend($message, $isSuccessful)
    {
        $event = new MailEvent(['message' => $message, 'isSuccessful' => $isSuccessful]);
        $this->trigger(self::EVENT_AFTER_SEND, $event);
    }
}
